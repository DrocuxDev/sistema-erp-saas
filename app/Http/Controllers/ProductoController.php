<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoPresentacionCompra;
use App\Models\ProductoPresentacionVenta;
use App\Models\ProductoProveedor;
use App\Models\Proveedor;
use App\Services\InventarioService;
use App\Support\ImagenUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductoController extends Controller
{
    /**
     * Listas PROVISIONALES para los selects del producto.
     * No existe tabla de catálogo para estos conceptos: son VARCHAR
     * + validación `in:`. Confirmar valores con el negocio antes de
     * considerarlas definitivas.
     */
    public const TIPOS_PRODUCTO = ['producto', 'servicio'];

    public const TIPOS_EXISTENCIA = ['mercaderia', 'producto_terminado', 'materia_prima', 'suministro', 'otro'];

    public const SEGUIMIENTOS = ['ninguno', 'lote', 'serie'];

    public const CONDICIONES = ['nuevo', 'usado', 'reacondicionado'];

    public const ENVASES = ['unidad', 'caja', 'paquete', 'blister', 'granel', 'otro'];

    public const TIPOS_AREA_PRODUCCION = ['ensamblaje', 'configuracion', 'reparacion', 'otro'];

    /**
     * Búsqueda servidor para el combobox de cotizaciones.
     * GET /productos-buscar?q=texto&limit=15
     * Insensible a mayúsculas/tildes, coincide con cualquier parte del nombre o SKU.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $limit = max(1, min(15, (int) $request->query('limit', 15)));

        $query = Producto::where('puede_venderse', true)
            ->with(['productoProveedores.proveedor']);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        // Traer un poco más para el filtrado sin tildes en PHP (MySQL LIKE no ignora tildes).
        $candidatos = $query->orderBy('nombre')->limit(60)->get();

        $normQ = self::normalizarBusqueda($q);
        $resultados = $candidatos
            ->filter(function ($p) use ($normQ) {
                if ($normQ === '') {
                    return true;
                }

                return str_contains(self::normalizarBusqueda($p->nombre.' '.$p->sku), $normQ);
            })
            ->take($limit)
            ->map(fn ($p) => self::formatoBusqueda($p))
            ->values();

        return response()->json($resultados);
    }

    /**
     * Creación rápida desde el combobox ("+ Crear producto nuevo").
     * POST /productos-rapido -> JSON del producto creado y seleccionado.
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'sku' => ['required', 'string', 'max:50', 'unique:productos,sku'],
            'unidad_medida' => ['nullable', 'string', 'max:20'],
            'precio_venta' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'precio_compra' => ['required', 'numeric', 'min:0', 'max:1000000', 'decimal:0,2'],
            'proveedor_id' => ['required', 'integer', Rule::exists('proveedores', 'id')->where('estado', 'activo')],
        ]);

        $producto = DB::transaction(function () use ($validated) {
            $producto = Producto::create([
                'sku' => $validated['sku'],
                'nombre' => $validated['nombre'],
                'unidad_medida' => ($validated['unidad_medida'] ?? null) ?: 'UND',
                'precio_venta' => $validated['precio_venta'] ?? null,
                'precio_compra' => $validated['precio_compra'],
                'stock_actual' => 0,
                'stock_minimo' => 0,
                'puede_venderse' => true,
                'puede_comprarse' => true,
            ]);
            $producto->productoProveedores()->create([
                'proveedor_id' => $validated['proveedor_id'],
                'precio_compra' => $validated['precio_compra'],
                'fecha_actualizacion' => today(),
            ]);

            return $producto;
        });

        $producto->load(['productoProveedores.proveedor']);

        return response()->json(self::formatoBusqueda($producto), 201);
    }

    private static function formatoBusqueda(Producto $p): array
    {
        return [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'sku' => $p->sku,
            'unidad_medida' => $p->unidad_medida,
            'precio_venta' => $p->precio_venta !== null ? (float) $p->precio_venta : null,
            'precio_compra' => $p->precio_compra !== null ? (float) $p->precio_compra : null,
            'proveedores' => $p->productoProveedores->map(fn ($pp) => [
                'id' => $pp->proveedor_id,
                'nombre' => $pp->proveedor?->nombre,
                'precio_compra' => (float) $pp->precio_compra,
            ])->values(),
        ];
    }

    private static function normalizarBusqueda(?string $texto): string
    {
        $texto = mb_strtolower($texto ?? '', 'UTF-8');
        $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);

        return trim($trans !== false ? $trans : $texto);
    }

    public function index(Request $request)
    {
        $query = Producto::query();

        // Filtro por tipo
        if ($request->filled('tipo')) {
            if ($request->tipo === 'vendible') {
                $query->where('puede_venderse', true);
            } elseif ($request->tipo === 'comprable') {
                $query->where('puede_comprarse', true);
            }
        }

        // Filtro por estado (disponibilidad)
        if ($request->filled('estado')) {
            if ($request->estado === 'bajo') {
                $query->whereColumn('stock_actual', '<=', 'stock_minimo');
            } elseif ($request->estado === 'disponible') {
                $query->whereColumn('stock_actual', '>', 'stock_minimo');
            }
        }

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('categoria', 'like', '%'.$request->categoria.'%');
        }

        $productos = $query->paginate(15)->withQueryString();

        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        return view('productos.create', $this->datosFormulario());
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge(
            [
                'sku' => ['required', 'string', 'max:50', 'unique:productos'],
                'nombre' => ['required', 'string', 'max:200'],
                'descripcion' => ['nullable', 'string', 'max:255'],
                'unidad_medida' => ['required', 'string', 'max:20'],
                'categoria' => ['nullable', 'string', 'max:100'],
                'stock_actual' => ['required', 'numeric', 'min:0', 'max:1000000', 'decimal:0,2'],
                'stock_minimo' => ['required', 'numeric', 'min:0'],
                'puede_venderse' => ['boolean'],
                'puede_comprarse' => ['boolean'],
            ],
            self::atributosComercialesRules(),
            ImagenUpload::rules()
        ));

        $stockInicial = (string) $validated['stock_actual'];
        unset($validated['stock_actual']);
        ImagenUpload::crear($request, 'productos', function ($ruta) use ($validated, $stockInicial) {
            $producto = Producto::create([...$validated, 'imagen' => $ruta]);
            if ((float) $stockInicial > 0) {
                app(InventarioService::class)->registrar($producto->id, 'entrada', $stockInicial, 'ajuste', null, now()->toDateString());
            }
        });

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado exitosamente');
    }

    public function show(Producto $producto)
    {
        $producto->load(['productoProveedores.proveedor', 'presentacionesCompra']);

        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $proveedoresDisponibles = Proveedor::where('estado', 'activo')->orderBy('nombre')->get();

        return view('productos.edit', array_merge(
            compact('producto', 'proveedoresDisponibles'),
            $this->datosFormulario()
        ));
    }

    /**
     * Listas y sugerencias compartidas por los formularios de producto.
     */
    protected function datosFormulario(): array
    {
        return [
            'marcas' => Producto::whereNotNull('marca')->distinct()->orderBy('marca')->pluck('marca'),
            'modelos' => Producto::whereNotNull('modelo')->distinct()->orderBy('modelo')->pluck('modelo'),
            'listas' => [
                'tipos_producto' => self::TIPOS_PRODUCTO,
                'tipos_existencia' => self::TIPOS_EXISTENCIA,
                'seguimientos' => self::SEGUIMIENTOS,
                'condiciones' => self::CONDICIONES,
                'envases' => self::ENVASES,
                'tipos_area' => self::TIPOS_AREA_PRODUCCION,
            ],
        ];
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate(array_merge(
            [
                'sku' => ['required', 'string', 'max:50', 'unique:productos,sku,'.$producto->id],
                'nombre' => ['required', 'string', 'max:200'],
                'descripcion' => ['nullable', 'string', 'max:255'],
                'unidad_medida' => ['required', 'string', 'max:20'],
                'categoria' => ['nullable', 'string', 'max:100'],
                'stock_minimo' => ['required', 'numeric', 'min:0'],
                'puede_venderse' => ['boolean'],
                'puede_comprarse' => ['boolean'],
                'eliminar_imagen' => ['nullable', 'boolean'],
            ],
            self::atributosComercialesRules(),
            ImagenUpload::rules()
        ));

        if ($request->has('stock_actual') && abs((float) $request->input('stock_actual') - (float) $producto->stock_actual) > 0.001) {
            throw ValidationException::withMessages(['stock_actual' => 'El stock se modifica únicamente mediante un ajuste de inventario.']);
        }

        ImagenUpload::actualizar($request, 'productos', $producto, $validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente');
    }

    /**
     * Reglas de los 13 atributos comerciales/logísticos (todos opcionales;
     * se_fabrica llega siempre como "0"/"1" por el hidden del formulario).
     */
    protected static function atributosComercialesRules(): array
    {
        return [
            'tipo_producto' => ['nullable', 'string', 'max:30', Rule::in(self::TIPOS_PRODUCTO)],
            'tipo_existencia' => ['nullable', 'string', 'max:50', Rule::in(self::TIPOS_EXISTENCIA)],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'precio_venta' => ['nullable', 'numeric', 'min:0'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'stock_maximo' => ['nullable', 'numeric', 'min:0'],
            'peso_bruto' => ['nullable', 'numeric', 'min:0'],
            'seguimiento' => ['nullable', 'string', 'max:20', Rule::in(self::SEGUIMIENTOS)],
            'condicion' => ['nullable', 'string', 'max:30', Rule::in(self::CONDICIONES)],
            'envase' => ['nullable', 'string', 'max:50', Rule::in(self::ENVASES)],
            'tipo_area_produccion' => ['nullable', 'string', 'max:50', Rule::in(self::TIPOS_AREA_PRODUCCION)],
            'se_fabrica' => ['boolean'],
        ];
    }

    public function destroy(Producto $producto)
    {
        ImagenUpload::eliminarModelo($producto);

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado exitosamente');
    }

    public function addProveedor(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
        ]);

        ProductoProveedor::updateOrCreate(
            ['producto_id' => $producto->id, 'proveedor_id' => $validated['proveedor_id']],
            [
                'precio_compra' => $validated['precio_compra'],
                'fecha_actualizacion' => now()->toDateString(),
            ]
        );

        return redirect()->back()
            ->with('success', 'Proveedor asociado correctamente.');
    }

    public function removeProveedor(Producto $producto, int $proveedorId): RedirectResponse
    {
        ProductoProveedor::where('producto_id', $producto->id)
            ->where('proveedor_id', $proveedorId)
            ->delete();

        return redirect()->back()
            ->with('success', 'Proveedor desasociado correctamente.');
    }

    public function addPresentacion(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'unidad_medida' => [
                'required', 'string', 'max:20',
                Rule::unique('producto_presentaciones_compra', 'unidad_medida')
                    ->where(fn ($q) => $q->where('producto_id', $producto->id)),
            ],
            'equivalencia' => ['required', 'numeric', 'gt:0'],
            'preferida' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['preferida'])) {
            ProductoPresentacionCompra::where('producto_id', $producto->id)->update(['preferida' => false]);
        }

        ProductoPresentacionCompra::create([
            'producto_id' => $producto->id,
            'unidad_medida' => $validated['unidad_medida'],
            'equivalencia' => $validated['equivalencia'],
            'preferida' => $validated['preferida'] ?? false,
        ]);

        return redirect()->back()
            ->with('success', 'Presentación de compra agregada.');
    }

    public function removePresentacion(Producto $producto, int $presentacionId): RedirectResponse
    {
        ProductoPresentacionCompra::where('producto_id', $producto->id)
            ->where('id', $presentacionId)
            ->delete();

        return redirect()->back()
            ->with('success', 'Presentación de compra eliminada.');
    }

    public function preferPresentacion(Producto $producto, int $presentacionId): RedirectResponse
    {
        $existe = ProductoPresentacionCompra::where('producto_id', $producto->id)
            ->where('id', $presentacionId)
            ->exists();

        abort_unless($existe, 404);

        ProductoPresentacionCompra::where('producto_id', $producto->id)->update(['preferida' => false]);
        ProductoPresentacionCompra::where('producto_id', $producto->id)
            ->where('id', $presentacionId)
            ->update(['preferida' => true]);

        return redirect()->back()
            ->with('success', 'Presentación preferida actualizada.');
    }

    public function addPresentacionVenta(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'unidad_medida' => [
                'required', 'string', 'max:20',
                Rule::unique('producto_presentaciones_venta', 'unidad_medida')
                    ->where(fn ($q) => $q->where('producto_id', $producto->id)),
            ],
            'equivalencia' => ['required', 'numeric', 'gt:0'],
            'preferida' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['preferida'])) {
            ProductoPresentacionVenta::where('producto_id', $producto->id)->update(['preferida' => false]);
        }

        ProductoPresentacionVenta::create([
            'producto_id' => $producto->id,
            'unidad_medida' => $validated['unidad_medida'],
            'equivalencia' => $validated['equivalencia'],
            'preferida' => $validated['preferida'] ?? false,
        ]);

        return redirect()->back()
            ->with('success', 'Presentación de venta agregada.');
    }

    public function removePresentacionVenta(Producto $producto, int $presentacionId): RedirectResponse
    {
        ProductoPresentacionVenta::where('producto_id', $producto->id)
            ->where('id', $presentacionId)
            ->delete();

        return redirect()->back()
            ->with('success', 'Presentación de venta eliminada.');
    }

    public function preferPresentacionVenta(Producto $producto, int $presentacionId): RedirectResponse
    {
        $existe = ProductoPresentacionVenta::where('producto_id', $producto->id)
            ->where('id', $presentacionId)
            ->exists();

        abort_unless($existe, 404);

        ProductoPresentacionVenta::where('producto_id', $producto->id)->update(['preferida' => false]);
        ProductoPresentacionVenta::where('producto_id', $producto->id)
            ->where('id', $presentacionId)
            ->update(['preferida' => true]);

        return redirect()->back()
            ->with('success', 'Presentación preferida actualizada.');
    }
}

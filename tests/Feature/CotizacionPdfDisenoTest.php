<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Cotizacion;
use App\Models\CuentaBancaria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\ConfiguracionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CotizacionPdfDisenoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $vendedor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ConfiguracionSeeder::class);
        Storage::fake('public');
        $this->admin = User::factory()->create(['rol' => 'administrador']);
        $this->vendedor = User::factory()->create(['rol' => 'trabajador']);
    }

    private function crearCatalogo(): array
    {
        $cliente = Cliente::create([
            'nombre' => 'CLIENTE PRUEBA PDF',
            'razon_social' => 'CLIENTE PRUEBA PDF S.A.C.',
            'ruc' => '20111111111',
            'direccion' => 'Av. Prueba 123',
            'telefono' => '(01) 111-1111',
            'email' => 'prueba@cliente.com',
            'contacto' => 'Jefe de compras',
        ]);
        $proveedor = Proveedor::create(['nombre' => 'Proveedor Interno XYZ', 'ruc' => '20222222222']);
        $producto = Producto::create([
            'sku' => 'PDF-001',
            'nombre' => 'Cámara PTZ de prueba 20X',
            'unidad_medida' => 'UND',
            'puede_venderse' => true,
            'puede_comprarse' => true,
        ]);

        return [$cliente, $proveedor, $producto];
    }

    private function payloadLineas(int $n, $producto, $proveedor): array
    {
        $lineas = [];
        for ($i = 0; $i < $n; $i++) {
            $lineas[] = [
                'producto_id' => $producto->id,
                // Mismo producto en varias filas solo para la prueba de saltos;
                // la UI evita duplicados, aquí usamos productos distintos si existen.
                'proveedor_id' => $proveedor->id,
                'descripcion' => 'Detalle adicional del ítem '.($i + 1),
                'unidad_medida' => 'UND',
                'cantidad' => 1,
                'precio_venta_unit' => 100.00,
                'precio_compra_unit' => 60.00,
                'igv_compra' => 0,
            ];
        }

        return $lineas;
    }

    private function crearCotizacion(int $items = 2): Cotizacion
    {
        [$cliente, $proveedor, $producto] = $this->crearCatalogo();

        // Para la prueba de 25 ítems se crean productos distintos (la UI evita duplicados).
        $lineas = [];
        for ($i = 0; $i < $items; $i++) {
            $p = $i === 0 ? $producto : Producto::create([
                'sku' => 'PDF-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'nombre' => 'Producto de prueba '.($i + 1),
                'unidad_medida' => 'UND',
                'puede_venderse' => true,
                'puede_comprarse' => true,
            ]);
            $lineas[] = [
                'producto_id' => $p->id,
                'proveedor_id' => $proveedor->id,
                'descripcion' => 'Detalle adicional del ítem '.($i + 1),
                'unidad_medida' => 'UND',
                'cantidad' => 1,
                'precio_venta_unit' => 100.00,
                'precio_compra_unit' => 60.00,
                'igv_compra' => 0,
            ];
        }

        $response = $this->actingAs($this->vendedor)->post(route('cotizaciones.store'), [
            'cliente_id' => $cliente->id,
            'tipo_cotizacion' => 'producto',
            'asunto' => 'COTIZACIÓN PRUEBA PDF',
            'fecha' => '2026-10-06',
            'condiciones_adicionales' => 'Stock sujeto a disponibilidad.',
            'lineas' => $lineas,
        ]);
        $response->assertRedirect();

        return Cotizacion::with(['cliente', 'usuario', 'detalles.producto', 'resumen'])->firstOrFail();
    }

    public function test_store_guarda_snapshot_y_condiciones_adicionales(): void
    {
        $cotizacion = $this->crearCotizacion(2);

        $this->assertNotNull($cotizacion->documento_snapshot);
        $this->assertIsArray($cotizacion->documento_snapshot);
        $this->assertArrayHasKey('color_principal', $cotizacion->documento_snapshot);
        $this->assertArrayHasKey('texto_saludo', $cotizacion->documento_snapshot);
        $this->assertArrayHasKey('cuentas', $cotizacion->documento_snapshot);
        $this->assertEquals('Stock sujeto a disponibilidad.', $cotizacion->condiciones_adicionales);
    }

    public function test_pdf_html_tiene_la_estructura_pedida_y_sin_datos_internos(): void
    {
        CuentaBancaria::create([
            'banco' => 'BCP', 'moneda' => 'Soles', 'numero_cuenta' => '193-98765432-0-11',
            'cci' => '002-193-009876543201-14', 'mostrar_en_pdf' => true, 'orden' => 0,
        ]);
        $cotizacion = $this->crearCotizacion(2);

        $html = $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', ['cotizacion' => $cotizacion->id, 'html' => 1]))
            ->assertOk()
            ->getContent();

        // Secciones en orden.
        foreach (['COTIZACIÓN', 'ATENCIÓN:', 'PRESUPUESTO:', 'SON:', 'Términos y condiciones del servicio', 'Información de pago bancario'] as $seccion) {
            $this->assertStringContainsString($seccion, $html);
        }
        // Columnas de la tabla.
        foreach (['Item', 'Código', 'Detalles', 'Cant.', 'U.M.', 'Precio unitario', 'Sub total'] as $col) {
            $this->assertStringContainsString($col, $html);
        }
        // Detalle adicional en letra pequeña y 2 filas.
        $this->assertStringContainsString('Detalle adicional del ítem 1', $html);
        $this->assertEquals(2, substr_count($html, 'class="fila-item"'));
        // Datos internos NO visibles.
        $this->assertStringNotContainsString('Proveedor Interno XYZ', $html);
        $this->assertStringNotContainsString('P.U Compra', $html);
        $this->assertStringNotContainsString('Ganancia', $html);
        // Sin retención configurada -> sin fila de retención.
        $this->assertStringNotContainsString('Retención', $html);
        // Sin portada configurada -> el PDF empieza en el encabezado.
        $this->assertStringNotContainsString('class="portada', $html);
        // Caja de pago con la cuenta marcada como visible.
        $this->assertStringContainsString('193-98765432-0-11', $html);
        $this->assertStringContainsString('CCI:', $html);
    }

    public function test_pdf_con_25_items_genera_pdf_y_repite_estructura(): void
    {
        $cotizacion = $this->crearCotizacion(25);

        $html = $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', ['cotizacion' => $cotizacion->id, 'html' => 1]))
            ->assertOk()
            ->getContent();

        $this->assertEquals(25, substr_count($html, 'class="fila-item"'));
        $this->assertStringContainsString('<thead>', $html);

        $pdf = $this->actingAs($this->vendedor)->get(route('cotizaciones.pdf', $cotizacion));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('Content-Type'));
    }

    public function test_reimpresion_usa_snapshot_aunque_cambie_la_configuracion(): void
    {
        $cotizacion = $this->crearCotizacion(2);
        $colorOriginal = $cotizacion->documento_snapshot['color_principal'];

        Configuracion::where('activo', true)->update([
            'color_principal' => '#FF0000',
            'texto_saludo' => 'Saludo NUEVO {empresa} {cliente}',
        ]);

        $html = $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', ['cotizacion' => $cotizacion->id, 'html' => 1]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString($colorOriginal, $html);
        $this->assertStringNotContainsString('#FF0000', $html);
        $this->assertStringNotContainsString('Saludo NUEVO', $html);
    }

    public function test_portada_solo_cuando_hay_imagen_e_interruptor(): void
    {
        $cotizacion = $this->crearCotizacion(2);

        // Imagen A4 vertical generada (100x141 ≈ 1:1.41).
        $ruta = UploadedFile::fake()->image('portada.png', 100, 141)->store('documentos', 'public');

        // Con imagen pero interruptor apagado -> sin portada.
        Configuracion::where('activo', true)->update([
            'portada_imagen_path' => $ruta,
            'incluir_portada' => false,
        ]);
        $cotizacion->update(['documento_snapshot' => \App\Services\CotizacionDocumento::snapshot(Configuracion::where('activo', true)->first())]);
        $sinPortada = $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', ['cotizacion' => $cotizacion->id, 'html' => 1]))
            ->assertOk()->getContent();
        $this->assertStringNotContainsString('class="portada', $sinPortada);

        // Interruptor encendido -> con portada.
        Configuracion::where('activo', true)->update(['incluir_portada' => true]);
        $cotizacion->update(['documento_snapshot' => \App\Services\CotizacionDocumento::snapshot(Configuracion::where('activo', true)->first())]);
        $conPortada = $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', ['cotizacion' => $cotizacion->id, 'html' => 1]))
            ->assertOk()->getContent();
        $this->assertStringContainsString('class="portada', $conPortada);

        // El PDF binario también se genera con portada.
        $pdf = $this->actingAs($this->vendedor)->get(route('cotizaciones.pdf', $cotizacion));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('Content-Type'));
    }

    public function test_portada_rechaza_proporcion_no_a4(): void
    {
        $cuadrada = UploadedFile::fake()->image('mala.png', 200, 200);

        $this->actingAs($this->admin)->put(route('configuracion.update'), array_merge(
            $this->configValida(),
            ['portada' => $cuadrada]
        ))->assertSessionHasErrors('portada');
    }

    public function test_cuentas_bancarias_crud_y_mostrar_en_pdf(): void
    {
        // Crear visible y oculta.
        $this->actingAs($this->admin)->post(route('configuracion.cuentas.store'), [
            'banco' => 'BCP', 'moneda' => 'Soles', 'numero_cuenta' => '193-111', 'cci' => '002-111',
        ])->assertRedirect();
        $oculta = CuentaBancaria::create([
            'banco' => 'Banco Oculto', 'moneda' => 'Soles', 'numero_cuenta' => '000-000',
            'mostrar_en_pdf' => false,
        ]);

        $cotizacion = $this->crearCotizacion(2);
        $html = $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', ['cotizacion' => $cotizacion->id, 'html' => 1]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('BCP', $html);
        $this->assertStringContainsString('193-111', $html);
        $this->assertStringNotContainsString('Banco Oculto', $html);

        // Editar y eliminar.
        $cuenta = CuentaBancaria::where('banco', 'BCP')->firstOrFail();
        $this->actingAs($this->admin)->put(route('configuracion.cuentas.update', $cuenta), [
            'banco' => 'BCP Editado', 'moneda' => 'Soles', 'numero_cuenta' => '193-111',
            'mostrar_en_pdf' => '1', 'orden' => 0,
        ])->assertRedirect();
        $this->assertEquals('BCP Editado', $cuenta->fresh()->banco);

        $this->actingAs($this->admin)->delete(route('configuracion.cuentas.destroy', $oculta))->assertRedirect();
        $this->assertNull(CuentaBancaria::find($oculta->id));
    }

    public function test_pdf_ejemplo_usa_datos_ficticios_y_config_actual(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('configuracion.pdf-ejemplo', ['html' => 1]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('EMPRESA EJEMPLO', $html);
        $this->assertStringContainsString('PRESUPUESTO:', $html);
        $this->assertStringNotContainsString('Proveedor Interno', $html);

        $pdf = $this->actingAs($this->admin)->get(route('configuracion.pdf-ejemplo'));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('Content-Type'));
    }

    public function test_pdf_sin_pie_ni_numerado_en_ninguna_pagina(): void
    {
        $cotizacion = $this->crearCotizacion(2);

        $bin = $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', $cotizacion))
            ->assertOk()->getContent();

        $paginas = $this->textoPaginasPdf($bin);
        $this->assertGreaterThanOrEqual(1, count($paginas));
        foreach ($paginas as $texto) {
            // El pie combinaba número+versión y el numerado "Página X de Y".
            $this->assertStringNotContainsString('Página', $texto);
            $this->assertStringNotContainsString('COTIZACIÓN N°', $texto);
        }
    }

    public function test_pdf_con_portada_tampoco_lleva_pie_ni_numerado(): void
    {
        $ruta = UploadedFile::fake()->image('portada.png', 100, 141)->store('documentos', 'public');
        Configuracion::where('activo', true)->update([
            'portada_imagen_path' => $ruta,
            'incluir_portada' => true,
        ]);

        $cotizacion = $this->crearCotizacion(2);

        $bin = $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', $cotizacion))
            ->assertOk()->getContent();

        $paginas = $this->textoPaginasPdf($bin);
        $this->assertGreaterThanOrEqual(2, count($paginas));
        foreach ($paginas as $texto) {
            $this->assertStringNotContainsString('Página', $texto);
            $this->assertStringNotContainsString('COTIZACIÓN N°', $texto);
        }
    }

    public function test_encabezado_caja_4_celdas_numero_8_digitos_y_sin_texto_general(): void
    {
        $cotizacion = $this->crearCotizacion(2);

        $html = $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', ['cotizacion' => $cotizacion->id, 'html' => 1]))
            ->assertOk()->getContent();

        // Número serie + 8 dígitos en el encabezado.
        $this->assertStringContainsString('C001-00000001', $html);
        // Versión + tipo sin corchetes, en líneas separadas.
        $this->assertStringContainsString('VERSION: 001', $html);
        $this->assertStringContainsString('PRODUCTO / EQUIPAMIENTO', $html);
        $this->assertStringNotContainsString('[PRODUCTO', $html);
        // Sin logo configurado: celda vacía, sin texto suelto.
        $this->assertStringNotContainsString('>General<', $html);
        // Franja como PNG embebido (única imagen sin logo ni portada).
        $this->assertEquals(1, substr_count($html, 'data:image/png;base64,'));
        // Fila SON + totales debajo de la tabla.
        $this->assertStringContainsString('son-totales', $html);
        $this->assertStringContainsString('SON:', $html);
        // Términos con viñetas.
        $this->assertStringContainsString('• <strong>Plazo de entrega:</strong>', $html);
        // Cierre lado a lado con espacio de firma.
        $this->assertStringContainsString('cierre-tabla', $html);
        $this->assertStringContainsString('espacio-firma', $html);
    }

    public function test_retencion_con_signo_negativo_y_activable_por_cotizacion(): void
    {
        [$cliente, $proveedor, $producto] = $this->crearCatalogo();

        $response = $this->actingAs($this->vendedor)->post(route('cotizaciones.store'), [
            'cliente_id' => $cliente->id,
            'tipo_cotizacion' => 'producto',
            'asunto' => 'CON RETENCIÓN',
            'fecha' => '2026-10-07',
            'pct_retencion' => 10,
            'motivo_excepcion_tributaria' => 'Retención pactada con el cliente',
            'lineas' => [[
                'producto_id' => $producto->id,
                'proveedor_id' => $proveedor->id,
                'descripcion' => 'Ítem con retención',
                'unidad_medida' => 'UND',
                'cantidad' => 1,
                'precio_venta_unit' => 10000.00,
                'precio_compra_unit' => 6000.00,
                'igv_compra' => 0,
            ]],
        ]);
        $response->assertRedirect();
        $cotizacion = Cotizacion::firstOrFail();

        // 10% de 10000 = 1000 de retención.
        $this->assertEquals(1000.00, (float) $cotizacion->resumen->retencion);

        $html = $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', ['cotizacion' => $cotizacion->id, 'html' => 1]))
            ->assertOk()->getContent();

        // SUB TOTAL + IGV − RETENCIÓN = TOTAL verificable a simple vista.
        $this->assertStringContainsString('- S/ 1,000.00', $html);
        $this->assertStringContainsString('S/ 10,000.00', $html);
        $this->assertStringContainsString('S/ 1,800.00', $html);
        $this->assertStringContainsString('S/ 10,800.00', $html);
    }

    public function test_codigo_largo_no_se_parte_y_datos_sin_logo_ni_retencion_sin_huecos(): void
    {
        [$cliente, $proveedor] = $this->crearCatalogo();
        $producto = Producto::create([
            'sku' => 'SRV-DL380-G10',
            'nombre' => 'Servidor de prueba',
            'unidad_medida' => 'UND',
            'puede_venderse' => true,
            'puede_comprarse' => true,
        ]);

        $response = $this->actingAs($this->vendedor)->post(route('cotizaciones.store'), [
            'cliente_id' => $cliente->id,
            'tipo_cotizacion' => 'producto',
            'asunto' => 'SIN RETENCIÓN',
            'fecha' => '2026-10-07',
            'pct_retencion' => 0,
            'lineas' => [[
                'producto_id' => $producto->id,
                'proveedor_id' => $proveedor->id,
                'descripcion' => '',
                'unidad_medida' => 'UND',
                'cantidad' => 1,
                'precio_venta_unit' => 500.00,
                'precio_compra_unit' => 300.00,
                'igv_compra' => 0,
            ]],
        ]);
        $response->assertRedirect();
        $cotizacion = Cotizacion::firstOrFail();

        $html = $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', ['cotizacion' => $cotizacion->id, 'html' => 1]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('SRV-DL380-G10', $html);
        $this->assertStringContainsString('white-space: nowrap', $html);
        $this->assertStringNotContainsString('Retención', $html);
        $this->assertStringNotContainsString('>General<', $html);

        $pdf = $this->actingAs($this->vendedor)->get(route('cotizaciones.pdf', $cotizacion));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('Content-Type'));
    }

    public function test_12_items_repite_encabezado_y_cierre_completo_al_final(): void
    {
        CuentaBancaria::create([
            'banco' => 'BCP', 'moneda' => 'Soles', 'numero_cuenta' => '193-111',
            'mostrar_en_pdf' => true, 'orden' => 0,
        ]);
        $cotizacion = $this->crearCotizacion(12);

        $bin = $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', $cotizacion))
            ->assertOk()->getContent();

        $paginas = $this->textoPaginasPdf($bin);
        $total = count($paginas);
        $this->assertGreaterThanOrEqual(2, $total);

        // Encabezado repetido en cada página: "VERSION:" (celda de la caja)
        // y la imagen PNG de la franja dibujada.
        // (El título con tilde se fragmenta en el stream; se usa la imagen.)
        foreach ($paginas as $texto) {
            $this->assertEquals(1, substr_count($texto, 'VERSION:'));
            $this->assertEquals(1, preg_match_all('#/[A-Za-z0-9]+ Do(?![A-Za-z0-9])#', $texto));
        }
        // Cierre completo solo en la última página.
        for ($i = 0; $i < $total - 1; $i++) {
            $this->assertStringNotContainsString('PAGO BANCARIO', $paginas[$i]);
        }
        $this->assertStringContainsString('PAGO BANCARIO', $paginas[$total - 1]);
    }

    public function test_borrar_logo_elimina_archivo_y_ruta(): void
    {
        $logo = UploadedFile::fake()->image('logo.png', 200, 100);
        $this->actingAs($this->admin)->put(route('configuracion.update'), array_merge(
            $this->configValida(),
            ['logo' => $logo]
        ))->assertRedirect();

        $ruta = Configuracion::where('activo', true)->firstOrFail()->logo_path;
        $this->assertNotNull($ruta);
        Storage::disk('public')->assertExists($ruta);

        $this->actingAs($this->admin)->delete(route('configuracion.logo.destroy'))->assertRedirect();

        $this->assertNull(Configuracion::where('activo', true)->firstOrFail()->logo_path);
        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_borrar_portada_elimina_archivo_y_apaga_interruptor(): void
    {
        $portada = UploadedFile::fake()->image('portada.png', 100, 141);
        $this->actingAs($this->admin)->put(route('configuracion.update'), array_merge(
            $this->configValida(),
            ['portada' => $portada, 'incluir_portada' => '1']
        ))->assertRedirect();

        $config = Configuracion::where('activo', true)->firstOrFail();
        $this->assertNotNull($config->portada_imagen_path);
        Storage::disk('public')->assertExists($config->portada_imagen_path);

        $this->actingAs($this->admin)->delete(route('configuracion.portada.destroy'))->assertRedirect();

        $config->refresh();
        $this->assertNull($config->portada_imagen_path);
        $this->assertFalse((bool) $config->incluir_portada);
    }

    public function test_trabajador_no_accede_a_configuracion_ni_ejemplo(): void
    {
        $this->actingAs($this->vendedor)->get(route('configuracion.edit'))->assertForbidden();
        $this->actingAs($this->vendedor)->get(route('configuracion.pdf-ejemplo'))->assertForbidden();
        $this->actingAs($this->vendedor)->delete(route('configuracion.logo.destroy'))->assertForbidden();
        $this->actingAs($this->vendedor)->delete(route('configuracion.portada.destroy'))->assertForbidden();
    }

    public function test_borrar_imagenes_conserva_las_de_cotizaciones_emitidas(): void
    {
        $this->comprobarImagenesHistoricas(false);
    }

    public function test_reemplazar_imagenes_conserva_las_de_cotizaciones_emitidas(): void
    {
        $this->comprobarImagenesHistoricas(true);
    }

    private function comprobarImagenesHistoricas(bool $reemplazar): void
    {
        $this->actingAs($this->admin)->put(route('configuracion.update'), array_merge(
            $this->configValida(),
            [
                'logo' => UploadedFile::fake()->image('logo.png', 200, 100),
                'portada' => UploadedFile::fake()->image('portada.png', 100, 141),
                'incluir_portada' => '1',
            ]
        ))->assertSessionHasNoErrors();
        $cotizacion = $this->crearCotizacion();
        $antes = \App\Services\CotizacionDocumento::datosPdf($cotizacion);
        $this->assertNotNull($antes['logoBase64']);
        $this->assertTrue($antes['conPortada']);

        if ($reemplazar) {
            $this->actingAs($this->admin)->put(route('configuracion.update'), array_merge(
                $this->configValida(),
                [
                    'logo' => UploadedFile::fake()->image('nuevo-logo.png', 300, 100),
                    'portada' => UploadedFile::fake()->image('nueva-portada.png', 200, 282),
                    'incluir_portada' => '1',
                ]
            ))->assertSessionHasNoErrors();
        } else {
            $this->actingAs($this->admin)->delete(route('configuracion.logo.destroy'))->assertRedirect();
            $this->actingAs($this->admin)->delete(route('configuracion.portada.destroy'))->assertRedirect();
        }

        $despues = \App\Services\CotizacionDocumento::datosPdf($cotizacion->fresh());
        $this->assertSame($antes['logoBase64'], $despues['logoBase64']);
        $this->assertSame($antes['portadaBase64'], $despues['portadaBase64']);
        $this->assertTrue($despues['conPortada']);
        $config = Configuracion::where('activo', true)->firstOrFail();
        $this->assertNotSame($cotizacion->documento_snapshot['logo_path'], $config->logo_path);
        $this->assertNotSame($cotizacion->documento_snapshot['portada_imagen_path'], $config->portada_imagen_path);
        $this->actingAs($this->vendedor)
            ->get(route('cotizaciones.pdf', ['cotizacion' => $cotizacion->id, 'html' => 1]))
            ->assertOk()->assertSee($antes['logoBase64'], false)->assertSee($antes['portadaBase64'], false);
    }

    /**
     * Texto por página del PDF en UTF-8. El cuerpo y el pie usan la fuente
     * core Helvetica (WinAnsi, sin subset): se convierte a UTF-8 para
     * poder buscar tildes sin falsos negativos.
     */
    private function textoPaginasPdf(string $bin): array
    {
        preg_match_all('/(\d+) 0 obj\b(.*?)(?:stream\r?\n(.*?)endstream\s*)?endobj/s', $bin, $m, PREG_SET_ORDER);
        $streams = [];
        $pages = [];
        foreach ($m as $obj) {
            $num = (int) $obj[1];
            $raw = $obj[3] ?? null;
            if ($raw !== null && $raw !== '') {
                $dec = @gzinflate(substr($raw, 2));
                if ($dec === false) {
                    $dec = @gzinflate($raw);
                }
                $streams[$num] = $dec !== false ? $dec : $raw;
            }
            if (preg_match('#/Type\s*/Page(?!s)#', $obj[2])) {
                preg_match_all('/(\d+) 0 R/', $obj[2], $refs);
                $pages[$num] = array_map('intval', $refs[1]);
            }
        }
        $out = [];
        foreach ($pages as $refs) {
            $txt = '';
            foreach ($refs as $r) {
                if (isset($streams[$r]) && is_string($streams[$r])) {
                    $txt .= $streams[$r];
                }
            }
            $out[] = mb_convert_encoding($txt, 'UTF-8', 'Windows-1252');
        }

        return $out;
    }

    private function configValida(): array
    {
        $c = Configuracion::where('activo', true)->firstOrFail();

        return [
            'nombre' => $c->nombre,
            'pct_igv' => 18,
            'pct_retencion' => 0,
            'pct_comision_venta' => 5,
            'pct_comision_ganancia' => 10,
        ];
    }
}


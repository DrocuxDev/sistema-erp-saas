<?php

namespace App\Observers;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AuditoriaObserver
{
    public function created(Model $model): void
    {
        $this->registrar($model, 'creacion', 'Creación del registro #'.$model->getKey(), [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $estado = $model->getAttribute('estado');
        if (in_array($estado, ['anulado', 'anulada'], true)) {
            $this->registrar($model, 'anulacion', 'Anulación del registro #'.$model->getKey(), $this->anteriores($model), $model->getChanges());

            return;
        }

        $this->registrar($model, 'edicion', 'Edición del registro #'.$model->getKey(), $this->anteriores($model), $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->registrar($model, 'eliminacion', 'Eliminación del registro #'.$model->getKey(), $model->getAttributes(), []);
    }

    private function anteriores(Model $model): array
    {
        $anteriores = [];
        foreach (array_keys($model->getChanges()) as $campo) {
            $anteriores[$campo] = $model->getRawOriginal($campo);
        }

        return $anteriores;
    }

    protected function registrar(Model $model, string $accion, string $detalle, array $antes, array $despues): void
    {
        try {
            $usuarioId = Auth::id();
            foreach (['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'] as $secreto) {
                unset($antes[$secreto], $despues[$secreto]);
            }

            Auditoria::create([
                'tabla' => $model->getTable(),
                'registro_id' => $model->getKey(),
                'usuario_id' => $usuarioId,
                'accion' => $accion,
                'detalle' => substr($detalle.' en '.$model->getTable(), 0, 255),
                'datos_anteriores' => $antes,
                'datos_nuevos' => $despues,
            ]);
        } catch (Throwable $e) {
            Log::critical('No se pudo registrar auditoría; la operación debe abortarse', [
                'tabla' => $model->getTable(),
                'registro_id' => $model->getKey(),
                'accion' => $accion,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('No se pudo registrar la auditoría; la operación fue interrumpida.', 0, $e);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Mostrar el dashboard de administración
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * Aprobar un alquiler
     */
    public function aprobar($id)
    {
        try {
            // Aquí iría tu lógica para aprobar el alquiler
            // $alquiler = Alquiler::findOrFail($id);
            // $alquiler->update(['estado' => 'aprobado']);

            return response()->json([
                'success' => true,
                'message' => 'Alquiler aprobado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar un alquiler
     */
    public function rechazar($id)
    {
        try {
            // Aquí iría tu lógica para rechazar el alquiler
            // $alquiler = Alquiler::findOrFail($id);
            // $alquiler->update(['estado' => 'rechazado']);

            return response()->json([
                'success' => true,
                'message' => 'Alquiler rechazado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar la vista de gestión de usuarios
     */
    public function usuarios()
    {
        return view('admin.usuarios');
    }

    /**
     * Filtrar usuarios por rol, estado o búsqueda
     */
    public function filtrarUsuarios(Request $request)
    {
        try {
            $query = collect();

            // Datos de ejemplo (reemplazar con BD real)
            $usuariosEjemplo = [
                ['id' => 1, 'nombre' => 'Carlos García', 'email' => 'carlos.garcia@email.com', 'rol' => 'arrendador', 'rolLabel' => 'Arrendador', 'estado' => 'activo', 'propiedades' => 3, 'fechaRegistro' => '12 ene 2025', 'avatarColor' => '#B8CCE4', 'avatarText' => 'CG'],
                ['id' => 2, 'nombre' => 'Laura Martínez', 'email' => 'laura.martinez@email.com', 'rol' => 'inquilino', 'rolLabel' => 'Inquilino', 'estado' => 'activo', 'propiedades' => 0, 'fechaRegistro' => '08 ene 2025', 'avatarColor' => '#A8D5BF', 'avatarText' => 'LM'],
                ['id' => 3, 'nombre' => 'Sofía Rodríguez', 'email' => 'sofia.rodriguez@email.com', 'rol' => 'arrendador', 'rolLabel' => 'Arrendador', 'estado' => 'inactivo', 'propiedades' => 1, 'fechaRegistro' => '15 dic 2024', 'avatarColor' => '#F9E4A0', 'avatarText' => 'SR'],
                ['id' => 4, 'nombre' => 'Pedro Molina', 'email' => 'pedro.molina@email.com', 'rol' => 'gestor', 'rolLabel' => 'Gestor', 'estado' => 'activo', 'propiedades' => 0, 'fechaRegistro' => '20 dic 2024', 'avatarColor' => '#E8D5F0', 'avatarText' => 'PM'],
                ['id' => 5, 'nombre' => 'Ana Torres', 'email' => 'ana.torres@email.com', 'rol' => 'miembro', 'rolLabel' => 'Miembro', 'estado' => 'activo', 'propiedades' => 0, 'fechaRegistro' => '03 ene 2025', 'avatarColor' => '#FFD5CC', 'avatarText' => 'AT'],
                ['id' => 6, 'nombre' => 'Miguel Fernández', 'email' => 'miguel.fernandez@email.com', 'rol' => 'admin', 'rolLabel' => 'Admin', 'estado' => 'activo', 'propiedades' => 0, 'fechaRegistro' => '01 ene 2025', 'avatarColor' => '#CCE5FF', 'avatarText' => 'MF'],
                ['id' => 7, 'nombre' => 'Elena Vargas', 'email' => 'elena.vargas@email.com', 'rol' => 'arrendador', 'rolLabel' => 'Arrendador', 'estado' => 'activo', 'propiedades' => 2, 'fechaRegistro' => '18 nov 2024', 'avatarColor' => '#D5F5E3', 'avatarText' => 'EV'],
                ['id' => 8, 'nombre' => 'Javier Ruiz', 'email' => 'javier.ruiz@email.com', 'rol' => 'inquilino', 'rolLabel' => 'Inquilino', 'estado' => 'inactivo', 'propiedades' => 0, 'fechaRegistro' => '05 dic 2024', 'avatarColor' => '#FAD7D7', 'avatarText' => 'JR'],
                ['id' => 9, 'nombre' => 'Carmen López', 'email' => 'carmen.lopez@email.com', 'rol' => 'miembro', 'rolLabel' => 'Miembro', 'estado' => 'activo', 'propiedades' => 0, 'fechaRegistro' => '10 ene 2025', 'avatarColor' => '#D7EAF9', 'avatarText' => 'CL'],
                ['id' => 10, 'nombre' => 'Roberto Mora', 'email' => 'roberto.mora@email.com', 'rol' => 'arrendador', 'rolLabel' => 'Arrendador', 'estado' => 'activo', 'propiedades' => 5, 'fechaRegistro' => '22 oct 2024', 'avatarColor' => '#FDE8C8', 'avatarText' => 'RM'],
            ];

            $usuarios = collect($usuariosEjemplo);

            // Filtro por rol
            if ($request->has('rol') && $request->rol !== '') {
                $usuarios = $usuarios->filter(function ($u) use ($request) {
                    return $u['rol'] === $request->rol;
                });
            }

            // Filtro por estado
            if ($request->has('estado') && $request->estado !== '') {
                $usuarios = $usuarios->filter(function ($u) use ($request) {
                    return $u['estado'] === $request->estado;
                });
            }

            // Búsqueda por nombre o email
            if ($request->has('q') && $request->q !== '') {
                $busqueda = strtolower($request->q);
                $usuarios = $usuarios->filter(function ($u) use ($busqueda) {
                    return stripos($u['nombre'], $busqueda) !== false ||
                        stripos($u['email'], $busqueda) !== false;
                });
            }

            return response()->json([
                'usuarios' => $usuarios->values()->all(),
                'total' => $usuarios->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alternar estado activo/inactivo de un usuario
     */
    public function toggleEstado($id)
    {
        try {
            // Aquí iría tu lógica con BD real
            // $usuario = Usuario::findOrFail($id);
            // $usuario->activo = !$usuario->activo;
            // $usuario->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado del usuario actualizado'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar usuarios a CSV
     */
    public function exportarUsuarios()
    {
        try {
            // Datos de ejemplo
            $usuariosEjemplo = [
                ['nombre' => 'Carlos García', 'email' => 'carlos.garcia@email.com', 'rol' => 'Arrendador', 'estado' => 'Activo', 'propiedades' => 3, 'registro' => '12 ene 2025'],
                ['nombre' => 'Laura Martínez', 'email' => 'laura.martinez@email.com', 'rol' => 'Inquilino', 'estado' => 'Activo', 'propiedades' => 0, 'registro' => '08 ene 2025'],
                ['nombre' => 'Sofía Rodríguez', 'email' => 'sofia.rodriguez@email.com', 'rol' => 'Arrendador', 'estado' => 'Inactivo', 'propiedades' => 1, 'registro' => '15 dic 2024'],
            ];

            $filename = 'usuarios_' . now()->format('Y-m-d_His') . '.csv';

            return response()->streamDownload(function () use ($usuariosEjemplo) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Nombre', 'Email', 'Rol', 'Estado', 'Propiedades', 'Registro']);

                foreach ($usuariosEjemplo as $usuario) {
                    fputcsv($output, $usuario);
                }

                fclose($output);
            }, $filename, [
                'Content-Type' => 'text/csv;charset=UTF-8',
                'Content-Disposition' => 'attachment;filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar la vista de gestión de propiedades
     */
    public function propiedades()
    {
        return view('admin.propiedades');
    }

    /**
     * Filtrar propiedades por estado, ciudad, precio o búsqueda
     */
    public function filtrarPropiedades(Request $request)
    {
        try {
            // Datos de ejemplo (reemplazar con BD real)
            $propiedadesEjemplo = [
                ['id' => 1, 'direccion' => 'Calle Mayor 14', 'ciudad' => 'Madrid', 'cp' => '28001', 'estado' => 'alquilada', 'precio' => '$1.200/mes', 'color' => '#B8CCE4', 'arrendadorNombre' => 'Carlos García', 'inquilinosActuales' => 2, 'inquilinosTotales' => 3],
                ['id' => 2, 'direccion' => 'Gran Vía 22', 'ciudad' => 'Madrid', 'cp' => '28013', 'estado' => 'publicada', 'precio' => '$980/mes', 'color' => '#A8D5BF', 'arrendadorNombre' => 'Ana Torres', 'inquilinosActuales' => 0, 'inquilinosTotales' => 2],
                ['id' => 3, 'direccion' => 'Av. Diagonal 88', 'ciudad' => 'Barcelona', 'cp' => '08008', 'estado' => 'alquilada', 'precio' => '$1.500/mes', 'color' => '#F9E4A0', 'arrendadorNombre' => 'Elena Vargas', 'inquilinosActuales' => 1, 'inquilinosTotales' => 1],
                ['id' => 4, 'direccion' => 'Paseo de Gracia 5', 'ciudad' => 'Barcelona', 'cp' => '08007', 'estado' => 'publicada', 'precio' => '$2.200/mes', 'color' => '#FFD5CC', 'arrendadorNombre' => 'Roberto Mora', 'inquilinosActuales' => 0, 'inquilinosTotales' => 4],
                ['id' => 5, 'direccion' => 'Calle Serrano 47', 'ciudad' => 'Madrid', 'cp' => '28001', 'estado' => 'alquilada', 'precio' => '$1.800/mes', 'color' => '#D7EAF9', 'arrendadorNombre' => 'Carlos García', 'inquilinosActuales' => 1, 'inquilinosTotales' => 1],
                ['id' => 6, 'direccion' => 'Calle Colón 8', 'ciudad' => 'Valencia', 'cp' => '46004', 'estado' => 'borrador', 'precio' => '$750/mes', 'color' => '#EDE7F6', 'arrendadorNombre' => 'Isabel Sanz', 'inquilinosActuales' => 0, 'inquilinosTotales' => 0],
                ['id' => 7, 'direccion' => 'Alameda de Hércules 3', 'ciudad' => 'Sevilla', 'cp' => '41002', 'estado' => 'publicada', 'precio' => '$650/mes', 'color' => '#D5F5E3', 'arrendadorNombre' => 'Diego Guerrero', 'inquilinosActuales' => 0, 'inquilinosTotales' => 2],
                ['id' => 8, 'direccion' => 'Gran Vía 45', 'ciudad' => 'Bilbao', 'cp' => '48001', 'estado' => 'inactiva', 'precio' => '$900/mes', 'color' => '#FAD7D7', 'arrendadorNombre' => 'Miguel Fdez.', 'inquilinosActuales' => 0, 'inquilinosTotales' => 0],
                ['id' => 9, 'direccion' => 'Calle Pelai 12', 'ciudad' => 'Barcelona', 'cp' => '08001', 'estado' => 'alquilada', 'precio' => '$1.100/mes', 'color' => '#CCE5FF', 'arrendadorNombre' => 'Elena Vargas', 'inquilinosActuales' => 2, 'inquilinosTotales' => 2],
                ['id' => 10, 'direccion' => 'Calle Larios 7', 'ciudad' => 'Málaga', 'cp' => '29005', 'estado' => 'publicada', 'precio' => '$820/mes', 'color' => '#FDE8C8', 'arrendadorNombre' => 'Roberto Mora', 'inquilinosActuales' => 0, 'inquilinosTotales' => 3],
            ];

            $propiedades = collect($propiedadesEjemplo);

            // Filtro por estado
            if ($request->has('estado') && $request->estado !== '') {
                $propiedades = $propiedades->filter(function ($p) use ($request) {
                    return $p['estado'] === $request->estado;
                });
            }

            // Filtro por ciudad
            if ($request->has('ciudad') && $request->ciudad !== '') {
                $propiedades = $propiedades->filter(function ($p) use ($request) {
                    return strtolower($p['ciudad']) === strtolower($request->ciudad);
                });
            }

            // Filtro por precio (simulado)
            if ($request->has('precio') && $request->precio !== '') {
                // Lógica de rangos de precio
            }

            // Búsqueda por dirección o ciudad
            if ($request->has('q') && $request->q !== '') {
                $busqueda = strtolower($request->q);
                $propiedades = $propiedades->filter(function ($p) use ($busqueda) {
                    return stripos($p['direccion'], $busqueda) !== false ||
                        stripos($p['ciudad'], $busqueda) !== false;
                });
            }

            return response()->json([
                'propiedades' => $propiedades->values()->all(),
                'total' => $propiedades->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar una propiedad
     */
    public function desactivarPropiedad($id)
    {
        try {
            // Aquí iría tu lógica con BD real
            // $propiedad = Propiedad::findOrFail($id);
            // $propiedad->update(['estado' => 'inactiva']);

            return response()->json([
                'success' => true,
                'message' => 'Propiedad desactivada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una propiedad
     */
    public function eliminarPropiedad($id)
    {
        try {
            // Aquí iría tu lógica con BD real (soft delete recomendado)
            // $propiedad = Propiedad::findOrFail($id);
            // $propiedad->delete();

            return response()->json([
                'success' => true,
                'message' => 'Propiedad eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar propiedades a CSV
     */
    public function exportarPropiedades()
    {
        try {
            // Datos de ejemplo
            $propiedadesEjemplo = [
                ['direccion' => 'Calle Mayor 14', 'ciudad' => 'Madrid', 'arrendador' => 'Carlos García', 'estado' => 'Alquilada', 'precio' => '$1.200/mes'],
                ['direccion' => 'Gran Vía 22', 'ciudad' => 'Madrid', 'arrendador' => 'Ana Torres', 'estado' => 'Publicada', 'precio' => '$980/mes'],
                ['direccion' => 'Av. Diagonal 88', 'ciudad' => 'Barcelona', 'arrendador' => 'Elena Vargas', 'estado' => 'Alquilada', 'precio' => '$1.500/mes'],
            ];

            $filename = 'propiedades_' . now()->format('Y-m-d_His') . '.csv';

            return response()->streamDownload(function () use ($propiedadesEjemplo) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Dirección', 'Ciudad', 'Arrendador', 'Estado', 'Precio']);

                foreach ($propiedadesEjemplo as $propiedad) {
                    fputcsv($output, $propiedad);
                }

                fclose($output);
            }, $filename, [
                'Content-Type' => 'text/csv;charset=UTF-8',
                'Content-Disposition' => 'attachment;filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

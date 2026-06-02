# TallerMec — Sistema de Gestión de Taller Mecánico

## Pasos para levantar con XAMPP

1. Copiar la carpeta `Taller_mecanico` a `C:\xampp\htdocs\`
2. Iniciar Apache y MySQL desde el Panel de Control de XAMPP
3. Abrir **phpMyAdmin** (`http://localhost/phpmyadmin`)
4. Importar el archivo `sql/tallermec.sql` (crea la base de datos, tablas y datos de prueba)
5. Verificar la configuración de conexión en `config/db.php`:
   - `$host = 'localhost'`
   - `$usuario = 'root'`
   - `$password = ''`
   - `$base_datos = 'tallermec'`
6. Acceder a **http://localhost/Taller_mecanico**

---

## Usuarios de prueba

| Nombre          | Email                  | Contraseña | Rol          |
|-----------------|------------------------|------------|--------------|
| Admin Sistema   | admin@taller.com       | admin123   | admin        |
| Roberto Silva   | roberto@taller.com     | mec123     | mecanico     |
| Diego Torres    | diego@taller.com       | elec123    | electricista |
| Carlos Gomez    | carlos@cliente.com     | cli123     | cliente      |
| Maria Lopez     | maria@cliente.com      | cli456     | cliente      |

---

## Estructura del proyecto

```
Taller_mecanico/
├── index.php           # Login
├── logout.php          # Cierre de sesión
├── dashboard.php       # Panel principal (vistas por rol)
├── config/
│   └── db.php          # Configuración de base de datos
├── includes/
│   ├── header.php      # Cabecera HTML + Bootstrap CDN
│   ├── sidebar.php     # Barra lateral con navegación por rol
│   └── footer.php      # Pie + scripts Bootstrap
├── auth/
│   └── check_session.php   # Verificación de sesión activa
├── ordenes/
│   ├── listar.php      # Listado con filtros por estado
│   ├── crear.php       # Nueva orden (admin/cliente)
│   ├── editar.php      # Editar orden / cambiar estado
│   └── eliminar.php    # Eliminar (admin) / Cancelar (cliente)
├── vehiculos/
│   ├── listar.php      # Listado de vehículos
│   ├── crear.php       # Registrar vehículo
│   ├── editar.php      # Editar vehículo
│   ├── eliminar.php    # Eliminar vehículo
│   └── ajax_vehiculos.php  # Endpoint AJAX para select dinámico
├── usuarios/
│   ├── listar.php      # Listado de usuarios (solo admin)
│   ├── crear.php       # Crear usuario (solo admin)
│   ├── editar.php      # Editar usuario (solo admin)
│   └── eliminar.php    # Eliminar usuario (solo admin)
└── sql/
    └── tallermec.sql   # Script SQL completo
```

---

## Roles y permisos

| Acción                         | Admin | Mecánico | Electricista | Cliente |
|-------------------------------|:-----:|:--------:|:------------:|:-------:|
| Ver todos los usuarios        | ✓     |          |              |         |
| Crear/editar/eliminar usuarios| ✓     |          |              |         |
| Ver todos los vehículos       | ✓     |          |              |         |
| Ver sus vehículos             |       |          |              | ✓       |
| Registrar vehículo            | ✓     |          |              | ✓       |
| Ver todas las órdenes         | ✓     |          |              |         |
| Ver sus órdenes (por tipo)    |       | ✓        | ✓            |         |
| Ver sus órdenes (cliente)     |       |          |              | ✓       |
| Crear orden                   | ✓     |          |              | ✓       |
| Editar orden completa         | ✓     |          |              |         |
| Cambiar estado de su orden    |       | ✓        | ✓            |         |
| Cancelar orden propia         |       |          |              | ✓ (solo pendiente) |

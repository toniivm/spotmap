-- SpotMap - Lockdown de roles en Supabase
-- Objetivo:
-- 1) Dejar a tonivfortnite@gmail.com como único usuario admin
-- 2) Quitar privilegios moderador/admin al resto (incluidas cuentas e2e)
-- 3) Opcionalmente eliminar cuentas e2e de auth/profiles

begin;

-- Asegurar que todo profile tenga rol válido
update public.profiles
set role = 'user',
    updated_at = now()
where role is null or trim(role) = '' or role not in ('user','moderator','admin');

-- Quitar roles elevados a TODOS
update public.profiles
set role = 'user',
    updated_at = now()
where role in ('moderator','admin');

-- Subir a admin SOLO al correo objetivo
update public.profiles p
set role = 'admin',
    updated_at = now()
from auth.users u
where p.user_id = u.id
  and lower(u.email) = lower('tonivfortnite@gmail.com');

commit;

-- ===== Verificación =====
select u.email, p.role
from public.profiles p
join auth.users u on u.id = p.user_id
order by p.role desc, u.email asc;

-- ===== Opcional: eliminar cuentas e2e (descomenta si realmente no las quieres) =====
-- delete from public.profiles p
-- using auth.users u
-- where p.user_id = u.id
--   and lower(u.email) like 'e2e.%@spotmap.test';
--
-- delete from auth.users
-- where lower(email) like 'e2e.%@spotmap.test';

@echo off
set param1=%1

C:\xampp\php\php.exe -f .\controllers\SincronizaBloqueoClientes.php -- %param1%
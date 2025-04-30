<?php
$router = new \App\Router\Router();

// Новые маршруты (регистрируются через register())
$router->register('/avtoservis/book', [AppointmentController::class, 'bookAppointment'], ['GET', 'POST']);
$router->register('/avtoservis/history', [AppointmentController::class, 'getHistory']);

// Старые маршруты будут работать через handleLegacyRoute()
return $router;
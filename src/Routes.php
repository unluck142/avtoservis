<?php
$router = new \App\Router\Router();

// Новые маршруты (регистрируются через register())
$router->register('/avtoservis/confirm_booking', [OrderController::class, 'confirmBooking'], ['POST']);
$router->register('/avtoservis/history', [OrderController::class, 'history']);

// Старые маршруты будут работать через handleLegacyRoute()
return $router;

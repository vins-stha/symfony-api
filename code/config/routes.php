<?php
// config/routes.php
use App\Controller\NoteController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
	$routes->add('notes_list', '/api/v1/notes')
		->controller([NoteController::class, 'index'])
		->methods(['GET']);
	
	$routes->add('note_detailed_view', '/api/v1/notes/{id}')
		->controller([NoteController::class, 'index'])
		->methods(['GET']);
	
	$routes->add('note_edit', '/api/v1/notes/{id}')
		->controller([NoteController::class, 'update'])
		->methods(['PUT']);

	$routes->add('note_create', '/api/v1/notes/add')
		->controller([NoteController::class, 'create'])
		->methods(['POST']);
	
	$routes->add('note_delete', '/api/v1/notes/{id}')
		->controller([NoteController::class, 'delete'])
		->methods(['DELETE']);
};

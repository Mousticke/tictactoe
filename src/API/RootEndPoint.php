<?php namespace API;

use Core\Request;
use Core\Response;
use Core\EndPoint;
use API\Model;

class RootEndPoint extends EndPoint {
	private $model;

	public function __construct(){
		$this->model = null;
	}

	private function getModel(){
		if( $this->model == null )
			$this->model = new Model();

		return $this->model;
	}

	public function resolve(Request $request){
		$path = $request->getPathParts();

		// If requesting the root, redirect to the game
		if( empty($path) ){
			$response = new Response();
			$response->redirect(HOSTBASE . 'public');
			return $response;
		}

		if( $path == ['games'] ){
			return $this->doAllGames($request);
		} else if( count($path) == 2 && $path[0] == 'games' ){
			$game = $path[1];
			return $this->doGame($request, $game);
		}

		return null;
	}

	private function doAllGames($request){
		switch($request->getMethod()){
			case 'GET':
				return $this->doGetGames($request);

			case 'POST':
				return $this->doPostGames($request);

			default:
				return Response::makeJSON([
					'status' => 'fail'
				], 400);
		};

		$response = new Response();
		$response->printf('fuck');
		return $response;
	}

	private function doGetGames($request){
		$games = $this->getModel()->getAllGames();
		$gameJSON = [];

		foreach($games as $curr){
			$id = $curr['id'];

			array_push($gameJSON, [
				'id' => $id,
				'url' => HOSTBASE . "games/$id",
				'name' => $curr['name'],
				'size' => $curr['size'],
			]);
		}

		return Response::makeJSON([
			'status' => 'ok',
			'games' => $gameJSON,
		]);
	}

	private function doPostGames($request){
		try {
			$name = $request->getParams()->req('name');
			$size = $request->getParams()->get('size', 3);

			if( $this->getModel()->createGame($name, $size) ){
				return Response::makeJSON([
					'status' => 'ok'
				], 201);
			} else {
				return Response::makeJSON([
					'status' => 'already exists'
				], 409); // Conflict
			}
		} catch(\Exception $e){
			return Response::makeJSON([
				'status' => 'fail'
			], 400); // Bad Request
		}
	}

	private function doGame($request, $id){
		$game = $this->getModel()->getGameById($id);

		if( $game == null )
			return null;

		switch($request->getMethod()){
			case 'GET':
				return $this->doGetGame($request, $game);

			case 'POST':
				return $this->doPostGame($request, $game);

			case 'PUT':
				return $this->doPutGame($request, $game);

			case 'DELETE':
				return $this->doDeleteGame($request, $game);

			default:
				return Response::makeJSON([
					'status' => 'fail'
				], 400);
		}
	}

	private function doGetGame($request, $game){
		$turns = $this->getModel()->getTurnsForGame($game);
		$jsonTurns = [];

		foreach($turns as $curr){
			array_push($jsonTurns, [
				'player' => $curr['player'],
				'row' => $curr['row'],
				'col' => $curr['col'],
				'time' => $curr['time'],
			]);
		}

		$json = [
			'id' => $game['id'],
			'name' => $game['name'],
			'size' => $game['size'],
			'turns' => $jsonTurns,
		];

		return Response::makeJSON($json, 200);
	}

	private function doPostGame($request, $game){
		$turns = $this->getModel()->getTurnsForGame($game);

		// Retrieve parameters
		$player = $request->getParams()->get('player');
		$row    = $request->getParams()->get('row');
		$col    = $request->getParams()->get('col');

		if( $player === null || $row === null || $col === null ){
			return Response::makeJSON([
				'status' => 'fail'
			], 400);
		}

		// Make sure we're within the bounds of the board
		if( $row >= $game['size'] || $col >= $game['size'] )
			return Response::makeJSON([
				'status' => 'out of bounds'
			], 400);

		// Make sure the player exists
		if( $player != 1 && $player != 2 )
			return Response::makeJSON([
				'status' => 'invalid player'
			], 400);

		// Determine whose turn it is
		$currentPlayer = 1;

		if( ! empty($turns) ){
			$whoLastPlayed = $turns[count($turns) - 1]['player'];
			$currentPlayer = $whoLastPlayed == 1 ? 2 : 1;
		}

		// Make sure the right person is trying to play
		if( $currentPlayer != $player )
			return Response::makeJSON([
				'status' => 'wrong player',
			], 400);

		// Make sure there isn't already something there
		foreach($turns as $curr){
			if( $curr['col'] == $col && $curr['row'] == $row )
				return Response::makeJSON([
					'status' => 'cell already takne'
				], 400);
		}

		if( $this->getModel()->createTurn($game, $player, $row, $col) ){
			return Response::makeJSON([
				'status' => 'ok'
			], 201);
		} else {
			// This shouldn't fail, but just in case...
			return Response::makeJSON([
				'status' => 'server error'
			], 500);
		}
	}

	private function doPutGame($request, $game){
		$name = $request->getParams()->get('name');

		if( $name === null )
			return Response::makeJSON([
				'status' => 'fail'
			], 400);

		if( $this->getModel()->renameGame($game, $name) )
			return Response::makeJSON([
				'status' => 'ok'
			], 200);
		else
			return Response::makeJSON([
				'status' => 'server error'
			], 500);
	}

	private function doDeleteGame($request, $game){
		if( $this->getModel()->deleteGame($game) )
			return Response::makeJSON([
				'status' => 'ok'
			], 200);
		else
			return Response::makeJSON([
				'status' => 'server error'
			], 500);
	}
}


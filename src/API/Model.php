<?php namespace API;

use PDO;
use PDOException;

class Model {
	private $db;
	
	public function __construct(){
		$dsn = sprintf('mysql:dbname=%s;host=%s', DB_NAME, DB_HOST);
		
		try {
			
		} catch(PDOException $e){
			die("Failed to connect to database");
		}
		
		$this->db = new PDO($dsn, DB_USER, DB_PASS);
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	public function getAllGames(){
		$stmt = $this->db->prepare('SELECT * FROM games');
		$stmt->execute();
		
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function createGame($name, $size){
		$stmt = $this->db->prepare("INSERT INTO games ( name, size ) VALUES ( :name, :size )");
		$stmt->bindParam('name', $name);
		$stmt->bindParam('size', $size);
		
		try {
			$stmt->execute();
			return true;
		} catch( PDOException $e ){
			return false;
		}
	}
	
	public function getGameById($id){
		$stmt = $this->db->prepare('SELECT * FROM games WHERE id = :id');
		$stmt->bindParam('id', $id);
		
		try {
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			if( count($result) == 0 )
				return null;
			
			return $result[0];
		} catch( PDOException $e ){
			return null;
		}
	}
	
	public function getTurnsForGame($game){
		$stmt = $this->db->prepare('SELECT * FROM turns WHERE game = :game ORDER BY time');
		$stmt->bindParam('game', $game['id']);
		
		try {
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch( PDOException $e ){
			return null;
		}
	}
	
	public function createTurn($game, $player, $row, $col){
		$stmt = $this->db->prepare('INSERT INTO turns (game, player, row, col, time) VALUES (:game, :player, :row, :col, NOW())');
		$stmt->bindParam('game', $game['id']);
		$stmt->bindParam('player', $player);
		$stmt->bindParam('row', $row);
		$stmt->bindParam('col', $col);
		
		try {
			$stmt->execute();
			return true;
		} catch( PDOException $e ){
			return false;
		}
	}
	
	public function renameGame($game, $name){
		$stmt = $this->db->prepare('UPDATE games SET name = :name WHERE id = :game');
		$stmt->bindParam('game', $game['id']);
		$stmt->bindParam('name', $name);
		
		try {
			$stmt->execute();
			return true;
		} catch( PDOException $e ){
			return false;
		}
	}
	
	public function deleteGame($game){
		try {
			// Remove the turns for that game
			$stmt = $this->db->prepare('DELETE FROM turns WHERE game = :game');
			$stmt->bindParam('game', $game['id']);
			$stmt->execute();
			
			// Remove the game
			$stmt = $this->db->prepare('DELETE FROM games WHERE id = :game');
			$stmt->bindParam('game', $game['id']);
			$stmt->execute();
			return true;
		} catch( PDOException $e ){
			return false;
		}
		
	}
}


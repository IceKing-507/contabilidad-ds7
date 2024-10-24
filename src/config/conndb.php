<?php
class Database
{
	private static $conn = null;

	public static function getconnectiondb()
	{
		if (self::$conn === null) {
			try {
				self::$conn = new PDO("mysql:host=localhost;dbname=proyecto_db", "userdb", "passworddb");
				self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				
			}
		}
		return self::$conn;
	}
}

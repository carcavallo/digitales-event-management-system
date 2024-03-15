<?php

namespace controller;

use Exception;
use lib\DataRepo\DataRepo;
use model\User;
use function util\removeArrayKeys;
use function util\removeArrayValues;

class AuthController extends IOController
{
    public function login(): void
    {
        $this->checkPostArguments(["username", "password"]);

        $_POST["username"] = strtolower($_POST["username"]);

        $user = DataRepo::of(User::class)->select(
            where: [
                "username" => ["=" => $_POST["username"]],
            ]
        );

        if (empty($user) || !password_verify($_POST["password"], $user[0]->password)) {
            $this->writeLog("Login failed for the user {username} - Login data", ["username" => $_POST["username"]], 401);
            $this->sendResponse("error", "Username or password incorrect", null, 401);
        } else {
            $user[0]->last_login = time();

            DataRepo::update($user[0]);
    
            $_SESSION['user'] = $user[0]->toArray();
            unset($_SESSION['password']);
    
            $this->sendResponse("success", "Successfully logged in");
        }
    }

    public function register(): void
    {
        $arguments = removeArrayValues(User::getDbFields(), ["user_id", "role", "last_login", "created_at", "reset_token", "reset_token_expires"]);
        $this->checkPostArguments($arguments);

        $_POST["username"] = strtolower($_POST["username"]);
        $_POST["email"] = strtolower($_POST["email"]);
    
        $existingUsername = DataRepo::of(User::class)->select(
            where: [
                'username' => ['=' => $_POST["username"]]
            ]
        );
    
        if (!empty($existingUsername)) {
            $this->sendResponse("error", "Username already exists", null, 409);
            return;
        }
    
        $existingEmail = DataRepo::of(User::class)->select(
            where: [
                'email' => ['=' => $_POST["email"]]
            ]
        );
    
        if (!empty($existingEmail)) {
            $this->sendResponse("error", "Email already registered", null, 409);
            return;
        }
    
        $_POST["password"] = password_hash($_POST["password"], PASSWORD_DEFAULT);

        $user = User::fromArray($_POST);
        if (!DataRepo::insert($user)) {
            $this->sendResponse("error", "An error occurred during registration", null, 500);
            return;
        }
    
        $this->sendResponse("success", "Registration successful");
    }

    public function getSession()
    {
        if (isset($_SESSION['user']['user_id']) && $_SESSION['expires'] > time()) {
            $this->sendResponse("success", "User Session retrieved successfully", removeArrayKeys($_SESSION, ['password']));
        } else {
            $this->sendResponse("error", "Session expired or not logged in", null, 401);
        }
    }   
    
    public function logout(bool $respond = true): void
    {
        session_unset();

        if ($respond) {
            $this->sendResponse("success", "Successfully logged out");
        }
    }
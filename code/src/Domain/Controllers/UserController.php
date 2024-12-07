<?php

namespace Geekbrains\Application1\Domain\Controllers;

use Geekbrains\Application1\Application\Render;
use Geekbrains\Application1\Domain\Models\User;

class UserController {

    public function actionIndex(): string {
        $users = User::getAllUsersFromStorage();

        $render = new Render();

        if(!$users){
            return $render->renderPage(
                'user-empty.tpl', 
                [
                    'title' => 'Список пользователей в хранилище',
                    'message' => "Список пуст или не найден"
                ]);
        }
        else{
            return $render->renderPage(
                'user-index.tpl', 
                [
                    'title' => 'Список пользователей в хранилище',
                    'users' => $users
                ]);
        }
    }

    public function actionSave() {
        if(User::validateRequestData()) {
            $user = new User();
            $user->setParamsFromRequestData();
            $user->saveToStorage();

            // Вариант с переходом на список пользователей
            header('Location: ' . '/user');

            // Вариант с отображением конкретного пользователя
            //  $render = new Render();
            //  return $render->renderPage(
            //  'user-created.tpl',
            //  [
            //      'title' => 'Пользователь создан',
            //      'message' => "Создан пользователь " . $user->getUserName() . " " . $user->getUserLastName(),
            //      'user' => $user
            //  ]);

        }
        else {
            throw new \Exception("Переданные данные некорректны");
        }
    }

    public function actionUpdate(): string {

        $id = $this->getCorrectedId();

        if(User::exists($id)) {
            $user = new User();
            $user->setUserId($id);
            
            $arrayData = [];

            if(isset($_GET['name'])) $arrayData['user_name'] = $_GET['name'];
            if(isset($_GET['lastname'])) $arrayData['user_lastname'] = $_GET['lastname'];
            if(isset($_GET['birthday'])) $arrayData['user_birthday_timestamp'] = $_GET['birthday'];

            $user->updateUser($arrayData);

            $updatedUser = $user->getUserFromStorageById($id);

            $render = new Render();
            return $render->renderPage('user-created.tpl',
                [
                    'title' => 'Пользователь обновлен',
                    'message' => "Обновлен пользователь ",
                    'user' => $updatedUser
                ]);
        }
        else {
            throw new \Exception("Пользователь не существует");
        }
    }

    public function actionDelete() {

        $id = $this->getCorrectedId();

        if(User::exists($id)) {
            User::deleteFromStorage($id);

            // Вариант с переходом на список пользователей
             header('Location: ' . '/user');

            // Вариант с отображением информационного сообщения об удалении пользователя с конкретным id
            // $render = new Render();
            // return $render->renderPage('user-removed.tpl', ['id' => $id]);
        }
        else {
            throw new \Exception("Пользователь не существует");
        }
    }

    public function actionShow(): string {

        $id = $this->getCorrectedId();

        if(User::exists($id)) {
            $user = User::getUserFromStorageById($id);

            $render = new Render();
            return $render->renderPage(
                'user-created.tpl', [
                    'title' => 'Создан новый пользователь',
                    'message' => "Новый пользователь ",
                    'user' => $user
                ]
            );
        }
        else {
            throw new \Exception("Пользователь с таким id не существует");
        }
    }

    private function getCorrectedId(): int
    {
        return (isset($_GET['id'])) ? (int)$_GET['id'] : 0;
    }
}
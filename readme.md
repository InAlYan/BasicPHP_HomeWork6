 # Урок 6. Работа с БД

 ## Задание 1

    Мы стали работать с исключениями. Создайте в Render логику обработки исключений так, чтобы она встраивалась в общий шаблон. Вызов будет выглядеть примерно так:

```
try{
    $app = new Application();
    echo $app->run();
}
    catch(Exception $e){
    echo Render::renderExceptionPage($e);
}
```

### Решение

Ответ: основываясь на предложенной заготовке кода: что в классе Render появляется статический метод renderExceptionPage($e), в класс Render вношу изменения:

```
<?php

namespace Geekbrains\Application1\Application;

use Exception;
use Geekbrains\Application1\Domain\Models\Time;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class Render {

    private static string $viewFolder = '/src/Domain/Views/';
    private static FilesystemLoader $loader;
    private static Environment $environment;

    public function __construct(){
        Render::prepareEnv();
    }

    public function renderPage(string $contentTemplateName = 'page-index.tpl', array $templateVariables = []) {

        $template = Render::$environment->load('main.tpl');

        $templateVariables['content_template_name'] = $contentTemplateName;

        $templateVariables['content_template_cur_time'] = Time::getCurrentTime(); // Текущее время
        $templateVariables['content_template_header'] = 'site-header.tpl'; // Шапка
        $templateVariables['content_template_footer'] = 'site-footer.tpl'; // Подвал
        $templateVariables['content_template_sidebar'] = 'site-sidebar.tpl'; // Sidebar

        return $template->render($templateVariables);
    }

    public static function renderExceptionPage(Exception $exception): string {
        Render::prepareEnv();

        $templateVariables['content_template_name'] = "error.tpl";
        $templateVariables['error_message'] = $exception->getMessage();

        return Render::$environment->render("error.tpl", $templateVariables);
    }

    private static function prepareEnv() {
        Render::$loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . Render::$viewFolder);
        Render::$environment = new Environment(Render::$loader, [
//            'cache' => $_SERVER['DOCUMENT_ROOT'].'/cache/',  // Отключил кэш на время разработки
        ]);
    }
}
```

## Задание 2
    — Создайте метод обновления пользователя новыми данными. Например,
```
/user/update/?id=42&name=Петр
```

Такой вызов обновит имя у пользователя с ID 42. Обратите внимание, что остальные поля не меняются. Также помните, что пользователя с ID 42 может и не быть в базе.

### Решение

В контроллер UserController:

```
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
```
```
private function getCorrectedId(): int
{
    return (isset($_GET['id'])) ? (int)$_GET['id'] : 0;
}
```

В модель User:

```
public function updateUser(array $userDataArray) {
    $sql = "UPDATE users SET ";

    $counter = 0;
    foreach($userDataArray as $key => $value) {
        $sql .= $key ." = :".$key;

        if($counter != count($userDataArray)-1) {
            $sql .= ",";
        }

        $counter++;
    }
    $sql .= " WHERE id_user = :id_user";

    $userDataArray['id_user'] = $this->idUser;

    if(isset($userDataArray['user_birthday_timestamp'])) {
        $this->setBirthdayFromString($userDataArray['user_birthday_timestamp']);
        $userDataArray['user_birthday_timestamp'] = $this->userBirthday;
    }

    $handler = Application::$storage->get()->prepare($sql);
    $handler->execute($userDataArray);
}
```

Во view user-created.tpl:

```
<h3>{{ message }}</h3>

<ul>
    {% if (user.getUserId() is not same as (null)) %} <li>Id: {{ user.getUserId() }} </li> {% endif %}
    <li>Имя: {{ user.getUserName() }}</li>
    <li>Фамилия: {{ user.getUserLastName() }}</li>
    {% if (user.getUserBirthday() is not same as (null)) %} <li>День рождения: {{user.getUserBirthday() | date('d.m.Y')}} </li> {% endif %}
</ul>
```

## Задание 3

    — Создайте метод удаления пользователя из базы. Учитывайте, что пользователя может не быть в базе

```
/user/delete/?id=42 -->
```

### Решение

В контроллер UserController:

```
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
```

```
    private function getCorrectedId(): int
    {
        return (isset($_GET['id'])) ? (int)$_GET['id'] : 0;
    }
```

В модель User:

```
    public static function deleteFromStorage(int $user_id) : void {
        $sql = "DELETE FROM users WHERE id_user = :id_user";

        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute(['id_user' => $user_id]);
    }
```

Во view user-index.tpl:

```
<p>Список пользователей в хранилище</p>

<ul id="navigation">
    {% for user in users %}
       <li><a href="/user/show/?id={{user.getUserId()}}">{{user.getUserId()}}. {{ user.getUserName() }} {{ user.getUserLastName() }}. {% if (user.getUserBirthday() is not same as (null)) %} День рождения: {{user.getUserBirthday() | date('d.m.Y')}} {% endif %}</a></li>
    {% endfor %}
</ul>
```


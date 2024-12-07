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
Копируем к себе в проект в папку htdocs/web
после чего примеры становятся доступны по адресам
пример
http://pbr-wserv-rv.asmirnov.fenrir.immo/web/ex1/
http://pbr-wserv-rv.asmirnov.fenrir.immo/web/ex2/

Также необходимо подправить файлы
htdocs/web/rest/web/dispatch.php секция load, прописать имя своего проекта
в ex1 файл write.js исправить путь http://pbr-wserv-rv.asmirnov.fenrir.immo/web/rest/web/crud на нужный
в ex2 файл restful.js исправить путь http://pbr-wserv-rv.asmirnov.fenrir.immo/web/rest/web/crud на нужный

Также необходиимо добавить папку с ext-js /web/extjs-4.2.0 (см.ex1/index.html)
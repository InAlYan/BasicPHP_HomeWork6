<p>Список пользователей в хранилище</p>

<ul id="navigation">
    {% for user in users %}
       <li><a href="/user/show/?id={{user.getUserId()}}">{{user.getUserId()}}. {{ user.getUserName() }} {{ user.getUserLastName() }}. {% if (user.getUserBirthday() is not same as (null)) %} День рождения: {{user.getUserBirthday() | date('d.m.Y')}} {% endif %}</a></li>
    {% endfor %}
</ul>
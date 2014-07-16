Twig Defer Block
=============
This Twig extension adds a defer block rendering.
A defer block allows you to defer a block withing it's current context but show the output at a later stage in the template.
By default the defer block will append information based on it's reference name, this will allow you to define multiple defer block which will then be outputted at a single place.
 
Example
-------------
```jinja
{% defer js %}
    <script src="1.js" />
{% endblock %}
{% defer js %}
    <script src="2.js" />
{% endblock %}

<p>Hello</p>
{{ defer('js') }}
```

Output:
```html
<p>Hello</p>
    <script src="1.js" />
    <script src="2.js" />
```

Example with unique
-------------
When using the defer block a second name can be given. 
This name allows you to only render a block with that name once. (Remark: This will always pick the first block with the second name)  
```jinja
{% set bar = ['1', '2'] %}
{% for x in xs %}
    {% defer js once %}
        <script src="once.js" />
    {% endblock %}
    {% defer js %}
        <script src="{x}.js" />
    {% endblock %}
{% endfor %}

<p>Hello</p>
{{ defer('js') }}
```

Output:
```html
<p>Hello</p>
        <script src="once.js" />
        <script src="1.js" />
        <script src="2.js" />
```

Other options:
-------------
A different defer implementation was made by [Eugene Leonovich](https://github.com/rybakit) called [rybakit/twig-extensions-deferred](https://github.com/rybakit/twig-extensions-deferred).



 <!doctype html>
 <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

 <head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1">

     <!-- CSRF Token -->
     <meta name="csrf-token" content="{{ csrf_token() }}">

     <title>{{ config('app.name', 'Vue Laravel SPA') }}</title>

     <!-- Styles -->
     <link href="{{ asset('/css/app.css') }}" rel="stylesheet">

     <!-- Fonts -->
     <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
     <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
     <script type="text/javascript">
         window.csrf_token = "{{ csrf_token() }}"
     </script>
 </head>

 <body>
     <div id="app">
         <!-- v-appは全てのコンポーネントのルート -->
         <v-app>
             <!-- レイアウト -->
             <frame-component></frame-component>


         </v-app>
     </div>
     <!-- Scripts -->
     <script src="{{ asset('/js/app.js') }}" defer></script>
 </body>

 </html>
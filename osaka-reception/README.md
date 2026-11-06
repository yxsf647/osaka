# Osaka Wings Reception Display (PHP)

Run:  php -S 0.0.0.0:8080   then open http://localhost:8080 full-screen (F11) on the reception TV.
Or copy the folder into XAMPP/WAMP htdocs and open http://localhost/osaka-reception/

Show order: greeting + animated logo -> customer satisfaction & vehicles sold ->
hand-picked (featured) cars -> the rest of the inventory -> Financing -> Import Process.

- config.php        company details, colours, slide times, numbers, bottom-bar lines
- data/cars.php     car list ('featured' => true puts a car in the hand-picked group)
- data/info.php     Financing and Import Process slides
- assets/logo.png   save the logo here: the colour palette is then built from the logo
- assets/cars/      upload JPEG photos here (see assets/README.txt)

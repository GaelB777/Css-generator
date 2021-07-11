<?php
//options courtes
$folder = end($argv);



$shortopts = "r";  // n'accepte pas de valeur
$shortopts .= "i::"; // Optional value
$shortopts .= "s::"; // Optional value
$shortopts .= "p::"; // Optional value
$shortopts .= "o::";

$longopts  = array(
    "recursive",     // n'accepte pas de valeur
    "output-image=::",    // Optional value
    "output-style=::",   // Optional value
    "padding=::",
    "override-size=::"
);

if (count($argv) == 1) {

    $folder = "assets_folder";
}
if (file_exists($folder) == false) {

    $folder = "assets_folder";
}




$opts = getopt($shortopts, $longopts);



foreach($opts as $key => $value){

    array_slice($longopts, 1 );
    
}


var_dump($opts);
new CssGenerator($folder, $opts);

class MyImage
{
    public $imagecreate;
    public string $name;
    public int $height;
    public int $width;
    public int $position;

    public function __construct($name, $height, $width, $position, $imagecreate)
    {
        $this->name = $name;
        $this->height = $height;
        $this->width = $width;
        $this->position = $position;
        $this->imagecreate = $imagecreate;
    }
}

class CssGenerator
{
    protected $tab = array();
    private $position = 0;
    private $opts;
    private $dossierrecursif;
    private $sprite_argument2;
    private $stylecss_argument3;
    private $padding_argument;
    private $resize_argument;
    private $resizebool = false;
    public function __construct($folder, $opts)
    {
        $this->opts = $opts;
        $this->checkMyOpt();
        $this->sprite_add($folder);
    }



    private function checkMyOpt(): void
    {

        if (array_key_exists("r", $this->opts) || array_key_exists("recursive", $this->opts)) {
            $this->dossierrecursif = true;
        } else {

            $this->dossierrecursif = false;
        }


        if (array_key_exists("i", $this->opts) || array_key_exists("output-image=", $this->opts)) {

            if (!empty($this->opts["i"])) {

                $this->sprite_argument2 = $this->opts["i"];
            } elseif (!empty($this->opts["output-image="])) {

                $this->sprite_argument2 = $this->opts["output-image="];
            } else {

                $this->sprite_argument2 = "sprite.png";
            }
        } else {

            $this->sprite_argument2 = "sprite.png";
        }

        if (array_key_exists("s", $this->opts) || array_key_exists("output-style=", $this->opts)) {


            if (!empty($this->opts["s"])) {

                $this->stylecss_argument3 = $this->opts["s"];
            } elseif (!empty($this->opts["output-style="])) {

                $this->stylecss_argument3 = $this->opts["output-style="];
            } else {
                $this->stylecss_argument3 = "style.css";
            }
        } else {

            $this->stylecss_argument3 = "style.css";
        }


        if (array_key_exists("p", $this->opts) || array_key_exists("padding=", $this->opts)) {

            if (!empty($this->opts["p"])) {

                $this->padding_argument = $this->opts["p"];
            } elseif (!empty($this->opts["padding="])) {

                $this->padding_argument = $this->opts["padding="];
            } else {
                $this->padding_argument = 0;
            }
        } else {

            $this->padding_argument = 0;
        }
                  
        if (array_key_exists("o", $this->opts) || array_key_exists("override-size=", $this->opts)) {

            if (!empty($this->opts["o"])) {
                $this->resize_argument = $this->opts["o"];
                $this->resizebool = true;
            } elseif (!empty($this->opts["override-size="])) {

                $this->resize_argument = $this->opts["override-size="];
                $this->resizebool = true;
            }
        }
    }

    public function getTab(): array
    {
        return $this->tab;
    }
    public function getImagepng()
    {
        return $this->imagepng;
    }

    public function sprite_add($folder)
    {
        if (is_dir($folder)) {
            if ($opendirectory = opendir($folder)) {
                while (($file = readdir($opendirectory)) !== false) {
                    $path = $folder . '/' . $file;

                    if (is_dir($path) && $file != "." && $file != ".." && $this->dossierrecursif == true) {
                        $this->sprite_add($path);
                    } elseif (is_file($path) && exif_imagetype($path) == IMAGETYPE_PNG && $file != "." && $file != "..") {
                        $imagepng = imagecreatefrompng($path);
                        if ($this->resizebool == true) {

                            $largeur = $this->resize_argument;
                            $hauteur = $this->resize_argument;
                        } else {
    
                            $largeur = imagesx($imagepng);
                            $hauteur = imagesy($imagepng);
                        }

                        $file = ($path);
                        $myNewImage = new MyImage($file, $hauteur, $largeur, $this->position, $imagepng);
                        array_push($this->tab, $myNewImage);

                        $this->position += $largeur + $this->padding_argument;
                    }
                }
                closedir($opendirectory);
            }
        }
    }

    public function convert()
    {

        $hauteurFinale = 0;
        $largeurFinale = 0;

        foreach ($this->tab as $key) {
            $largeurFinale += $key->width + $this->padding_argument;
        }

        foreach ($this->tab as $key) {
            if ($key->height > $hauteurFinale) {
                $hauteurFinale = $key->height;
            }
        }

        $img = imagecreatetruecolor($largeurFinale, $hauteurFinale);
        $background = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $background);
        imagesavealpha($img, true);


        foreach ($this->tab as $key) {
            imagecopy(
                $img,             // $dst_im,
                $key->imagecreate, // $src_im,
                $key->position,   // $dst_x,
                0,                // $dst_y,
                0,                // $src_x,
                0,                // $src_y,
                $key->width,      // $src_w,
                $key->height      // $src_h
            );
        }
        if (!file_exists("css_generator")) {
            mkdir("css_generator");
            imagepng($img, "css_generator/$this->sprite_argument2");
        } else {
            imagepng($img, "css_generator/$this->sprite_argument2");
        }
    }

    public function createCss()
    {
        $px = "px";
        $i = 0;
        $tabsprite = array();
        foreach ($this->tab as $key) {
            $resultatCss = ".image$i{
 width: $key->width$px;
 height: $key->height$px;
 padding: $this->padding_argument$px;
 background: url('$this->sprite_argument2') -$key->position$px 0$px;
  

 }";
            $i++;
            $tabsprite[] = $resultatCss;
        }


        file_put_contents("css_generator/$this->stylecss_argument3", $tabsprite);


        // fclose($createFileCss);
    }
}

$sprite = new CssGenerator($folder, $opts);
$sprite->convert();
var_dump($sprite->getTab());
$sprite->createCss();












// var_dump($options);

// $dossier_argument1 = "";

// if (count($argv) < 4)
// {
//     $dossier_argument1 = "assets_folder";
//     $sprite_argument2 = "sprite.png";
//     $stylecss_argument3 = "style.css";
// }

// else
// {
//     $dossier_argument1 = $argv[1];
//     $sprite_argument2 = $argv[2];
//     $stylecss_argument3 = $argv[3];
// }







/*

  $position = 0;
           foreach ($this->tab as $key => $value) {
            $imagePNG = imagecreatefrompng($value);


            $imageSetting = [];
    
            $imageSetting['nom'] = $imagePNG; //$value = substr(strrchr($value, '/'), 1);
            $imageSetting['largeur'] = imagesx($imagePNG);
            $imageSetting['hauteur'] = imagesy($imagePNG);
            $imageSetting['position'] = $position;
            $position = $position + $imageSetting["largeur"];
    
            $tab[] = $imageSetting;

            
            /*$imagepng = imagecreatefrompng($value);
            $largeur = imagesx($imagepng);
            $hauteur = imagesy($imagepng);
            // $imgsize = getimagesize($file);
            // $imgname = basename($file);
            // $pathimg = $this->tab[$key];
            var_dump($value) . "\n";

 */   

       /*   
    public function imgconvert ($width, $height)
    {     
        
            foreach(Image::$tab as $key => $file){
                $pathimg = Image::$tab[$key];
                $imgsize = getimagesize($file);
                

            }
            

            $imgcreate = imagecreatefrompng($source);

            $container = imagecreatetruecolor($width,$height);

            $final = imagecopyresampled($container, $imgcreate, 0, 0, 0, 0, $width, $height, $imgsize[0], $imgsize[1]);

            imagepng($container, $dst);
            
    }

      
}

$tabimg=new Tableau();
//var_dump($tabimg);

*/

<?php
session_start();
include("db_connect.php");
?>

<!doctype html>
<html>
    
    <head>
        <title>Màj BDD</title>
        <meta charset="utf-8"  />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="shortcut icon" type="image/x-icon" href="../images/icon.ico" />
        
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        
        <link rel="stylesheet" href="style/style.css" />
    </head>
    
    <body>
        <?php
            include('header.php');
            ////////////////////////////////////
            //CLASS vector , tableau dynamique//
            ////////////////////////////////////
            class vector {
                private $_arrayList=array();
                private $_arrayList2=array();
               	private $_tailleTableau=0;
               
                public function add($data,$data2) {
                    $this->_arrayList[$this->_tailleTableau]=$data;
                    $this->_arrayList2[$this->_tailleTableau]=$data2;
                    $this->_tailleTableau++;
                }
                public function size(){
                	return $this->_tailleTableau;
                }
                public function getTab(){
                	return $this->_arrayList;
                }
                public function at1($i){
                	return $this->_arrayList[$i];
                }
                public function at2($i){
                	return $this->_arrayList2[$i];
                }
            } 

            ///////////////////////////////////////
            //Permet de chercher dans un truc USB//
            ///////////////////////////////////////
            function showDir($dir,$nbTabulation,&$vector)
            {
	            $files1 = scandir($dir);
	            for($j=0;$j<count($files1);$j++){
		            $value = $files1[$j];
		            //echo $value;
		            if($value != ".." && $value != '.' && $value != ".Trash-1000" && $value != "affiche"){
			            /////////////////////////////////////////////////
			            //Enleve le format de la video si besoin
			            //$value=explode(".",$value);
			            //$value=$value[0];
			            ////////////////////////////////////////////////
			
			            //echo $value;
			            /////ECHO LES TABULATIONS POUR FAIRE L'ARBRE
			            /*for($i=0;$i<$nbTab;$i++){
				            echo "	";
			            }*/
			            //echo $value."\n";
			            if(is_dir($dir."/".$value)){
				            //echo $dir."/".$value;
				            
				            showDir($dir."/".$value,$nbTabulation+1,$vector);
			            }
			            else{
			                $format=explode(".",$value);
			                $format=$format[1];
			                if ($format == "mp4" || $format == "ogv" || $format == "webm") {
				                $vector->add($value,$dir);
				            }
			            }
			            //echo $value;
		            }
	            }
	            return $vector;
            }

            //MAIN
            $rqtAfficher = mysqli_query($link, "SELECT * FROM films") or die(mysql_error());

            //Initialisation de mes variables
            $vectorFilmUSB = new vector; 
            $vectorFilmBDD = new vector; 
            $nbTab=0;
            $dir = '../Films';
            $dirAffiche = '../Films/affiche';
            $films = array();

            //vectorFilmUSB est rempli par le contenu du fichier contenu dasn le disque
            $vectorFilmUSB = showDir($dir,$nbTab,$vectorFilmUSB);


            //Affichage sur la page !
            print("</br>Film sur le disque :");
            $films = $vectorFilmUSB->getTab();
            for($i=0;$i<count($films);$i++)
            {
	            echo "</br>".$films[$i];
            }
            echo "\n";
            print("</br>Film dans la base de donnée :");
            while ($row = mysqli_fetch_assoc($rqtAfficher)) {
	            $titre=$row["titre"];
	            $chemin=$row["chemin"];
	            $vectorFilmBDD->add($titre,$chemin);
	            print(",".$titre);
            }
            //FIN Affichage sur la page !

            //Insertion dans la base de donnée si le film n'existe pas '
            for($i=0;$i<$vectorFilmUSB->size();$i++){
	            $existeDeja=false;
	            $nomFilm=explode(".",$vectorFilmUSB->at1($i));
	            $nomFilm=$nomFilm[0];
	            for($j=0;$j<$vectorFilmBDD->size();$j++){
		            if($nomFilm==$vectorFilmBDD->at1($j)){
			            $existeDeja = true;
			            echo "</br> Ce film existe deja : ".$nomFilm;
		            }
	            }
				$rqtInsertion = mysqli_prepare($link,"INSERT INTO `films`(`chemin`,`affiche`,`titre`) 
													VALUES ( ?, ?, ?)") or die(mysql_error());
	            if($existeDeja == false){
					$cheminFilm = "\"".$vectorFilmUSB->at2($i)."/".$vectorFilmUSB->at1($i)."\"";
					$cheminAffiche = "\"".$dirAffiche."/".$nomFilm.".jpg\"";
					$rqtInsertion->bind_param("sss",$cheminFilm, $cheminAffiche, $nomFilm);
					$rqtInsertion->execute();
		            echo "</br> Ajout de : ".$nomFilm;
	            }
			}
			// todo suprimer de la bdd les films qui ne sont plus sur le disque
        ?>

       <?php include('footer.html'); ?> 
    </body>
</html>

<?php
require_once(ABSPATH . "wp-includes/pluggable.php"); //Sinon erreur cannot modify header. A vérifier si toujours utile
require_once(ABSPATH . "wp-admin/includes/image.php"); //Pour importer des images

class Import {
    private static $newAds = 0;
    private static $updatedAds = 0;
    private static $errorAds = 0;
    private static $sendMail = false;

    public function showPage() {
        $optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");        
        $postType = get_current_screen()->post_type;
        $base = get_current_screen()->base;
        $dirPath = ABSPATH.$optionsImports["dirImportPath"];
        $logsURL = get_site_url()."/wp-content/plugins/".PLUGIN_RE_NAME."/logsImport.txt";
        $zipInDirPath = !empty(glob($dirPath.'*.{zip}', GLOB_BRACE));
        ?>
        <div class="wrap">
            <?php if($postType==="ad" && $base="bthimport") { ?>
                <h2>Importez les annonces</h2>
            <?php
            }
            if($zipInDirPath) { ?>
                <a href="?startImport" class="button button-primary" style="margin-right: 10px;">Lancer une importation</a>
            <?php }else{ ?>
                <form action="index.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="file" accept=".zip">
                    <p>
                        <input type="submit" name="submitImport" class="button button-primary" value="Importer les annonces">
                        <input type="checkbox" id="publishAds" name="publishAds" checked>
                        <label for="publishAds">Publier directement les annonces</label>                    
                    </p>      
                </form>
            <?php } ?>
        </div>
        <?php
        if(is_admin()) {
            if(isset($_POST["submitImport"])) {
                if(!is_dir($dirPath)) {
                    echo $dirPath;
                    mkdir($dirPath);
                }
                $filePath = $dirPath.basename($_FILES["file"]["name"]);
                $uploadOk = true;
                $imageFileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                if($imageFileType != "zip") {
                    echo "Seulement les fichiers .zip sont autorisés.";
                    $uploadOk = false;
                }

                if(!$uploadOk) {
                    echo " Le fichier n'a pas été envoyé.";
                }else{
                    if(move_uploaded_file($_FILES["file"]["tmp_name"], $filePath)){
                        echo "Le fichier a bien été envoyé.<br />";
                    }else{
                        echo "Le fichier n'a pas été envoyé.";
                    }
                }
            }

            if(isset($_GET["startImport"]) && $zipInDirPath || isset($_POST["submitImport"]) && !$zipInDirPath) {
                if(isset($_POST["submitImport"])) {
                    if(isset($_POST["publishAds"])) {
                        SELF::startImport();
                    }else{
                        SELF::startImport(false);
                    }
                }
                if(SELF::$newAds > 0 || SELF::$updatedAds > 0) {
                    echo "Importation réussie : ".SELF::$newAds." annonce(s) créée(s) et ".SELF::$updatedAds." annonce(s) mise(s) à jour.";
                }else if(SELF::$newAds === -1) {
                    echo "Il n'y a pas de zip à traiter.";
                }
                if(SELF::$errorAds !== 0) {
                    echo " Dont ".SELF::$errorAds." erreur(s). Consultez les logs pour en savoir plus.";
                }
            }
            if(isset($_GET["deleteLogs"])) {
                SELF::deleteLogs();
            }
            if(file_exists(plugin_dir_path(__DIR__)."logsImport.txt")) {
                ?>
                <br /><a target="_blank" href="<?=$logsURL;?>" style="margin-right: 15px;">Voir les logs</a><a href="?deleteLogs">Supprimer les logs</a>
                <?php
            }
        }
                
    }
    
    public function widgetImport() {
        wp_add_dashboard_widget(PLUGIN_RE_NAME."widgetImport", "Importer les annonces", array($this, "showPage"));
    }
    
    
    private static function limitNumberSaves() {
        $optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");
        $dirSavesPath = ABSPATH.$optionsImports["dirSavesPath"];
        $dirSaves = scandir($dirSavesPath); //Scan le dossier des sauvegardes
        $saves = array();
        foreach($dirSaves as $elem) {
            if((preg_match("/^\d{2}-\d{2}-\d{4}_\d{2}h\d{2}m\d{2}s_.+\.zip/", $elem))) { //Si c'est un fichier de sauvegarde
                array_push($saves, $elem); //On le met dans un tableau
            }
        }
        if(count($saves) >= $optionsImports["maxSaves"]) { //S'il y a plus de 2 fichiers de sauvegarde
            $latest_ctime = 0;
            foreach($saves as $save) {
                if(filectime($dirSavesPath.$save) > $latest_ctime) { 
                    $latest_ctime = filectime($dirSavesPath.$save);
                    $latest_filename = $save; //On prend le plus récent
                }
            }
            foreach($saves as $save) {
                if($save !== $latest_filename) {
                    if(!unlink($dirSavesPath.$save)) {//Et on supprime les autres
                        SELF::addLog("Impossible de supprimer les anciennes sauvegardes.");
                    }						
                }
            }
        }
    }
    
    private static function getPicture($url) {
        $retry = 0;
        do{
            $response = wp_remote_get($url);
            $retry++;
        }while(!is_array($response) && is_wp_error($response) && $retry < 10); //Tant qu'on a pas l'image ou qu'il y a moins de 10 essais, on tente de la récupérer
        return $response;
    }
    
    private static function setPicturesProperty($propertyWPId, $imgURL, $thumbnail=false) {
        $optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");
        $dirSavesPath = ABSPATH.$optionsImports["dirSavesPath"];
        $propertyImagesEmpty = true;
        $propertyImages = get_post_meta($propertyWPId, "adImages", true);
        $alreadyThisImage = false;
        if(!empty($propertyImages) && $propertyImages !== false) { //On vérifie s'il n'y a pas des images inscrites dans la BDD pour la galerie de la propriété
            $propertyImagesEmpty = false;

            $propertyImagesArray = explode(";", $propertyImages);
            foreach($propertyImagesArray as $propertyImage) { //On vérifie qu'on a pas déjà l'image
                if(get_post($propertyImage)->post_content === $imgURL) { //Remplacer par quelque chose qui compare vraiment les images ?
                    $alreadyThisImage = true;
                    break;
                }
            }
        }

        if($alreadyThisImage === false) { //Si on a pas l'image, on la récupère et on l'enregistre
            $img = SELF::getPicture($imgURL);
            if(is_array($img) && !is_wp_error($img) && wp_remote_retrieve_response_code($img) === 200) { //si on a bien réussi à la récupérer
                $imgOrigin = $img["body"];
                list($widthP, $heightP/*, $type, $attr*/) = getimagesizefromstring($imgOrigin); //On va la redimensionner et la convertir. Selon les paramètres définis
                $maxDim = $optionsImports["maxDim"]; //Parametrable
                if($widthP > $maxDim || $heightP > $maxDim) { //Si l'image est plus petit que $maxDim (px)
                    $ratio = $widthP/$heightP;
                    if($ratio > 1) {
                        $newWidth = $maxDim;
                        $newHeighteight = $maxDim/$ratio;
                    }else{
                        $newWidth = $maxDim*$ratio;
                        $newHeighteight = $maxDim;
                    }
                }else{
                    $newWidth = $widthP;
                    $newHeighteight = $heightP;
                }

                $src = imagecreatefromstring($imgOrigin);
                $dst = imagecreatetruecolor($newWidth, $newHeighteight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeighteight, $widthP, $heightP);
                imagedestroy($src);
                $fileName = uniqid("propertyPicture", true);
                $pathImg = $dirSavesPath.$fileName.".jpg";
                imagejpeg($dst, $pathImg, 85);
                imagedestroy($dst);

                $uploadThumbnail = wp_upload_bits($fileName.".jpg", null, @file_get_contents($pathImg)); //On l'enregistre dans la BDD
                //wp_unique_filename ?
                unlink($pathImg);
                if(!$uploadThumbnail["error"]) {
                    $attachment = array(
                        'post_author' 	=> 1,
                        'post_mime_type' => 'image/jpeg',
                        'post_parent'    => $propertyWPId,
                        'post_title'     => $fileName,
                        'post_content'   => $imgURL,
                        'post_status'    => 'inherit'
                    );
                    $attachmentId = wp_insert_attachment($attachment, $uploadThumbnail["file"], $propertyWPId, true);
                    
                    if(!SELF::checkError($attachmentId, "Impossible d'enregistrer l'image $imgURL dans la BDD pour l'annonce $propertyWPId (id BDD)")) {
                        if($attachmentData = wp_generate_attachment_metadata($attachmentId, $uploadThumbnail["file"])) {
                            wp_update_attachment_metadata($attachmentId, $attachmentData);
                        }else{
                            SELF::addLog("Impossible de mettre à jour les metadonnées de l'image $imgURL");
                            SELF::$errorAds++;
                        }

                        if($thumbnail) {
                            if(!set_post_thumbnail($propertyWPId, $attachmentId)) {
                                SELF::addLog("Impossible d'ajouter la miniature à l'annonce $propertyWPId (id BDD)");
                                SELF::$errorAds++;
                            }
                        }
                        if($propertyImagesEmpty === false) {
                            $attachmentId = $propertyImages.";".$attachmentId;
                        }
                        if(!update_post_meta($propertyWPId, "adImages", $attachmentId)) { //Important de mettre les ids dans un meta car sinon on peut retrouver seulement les photos uploadées AVEC le post. Ca ne marche pas si elles sont choisies dans la galerie.
                            SELF::addLog("Impossible d'ajouter l'image $imgURL à la galerie d'images de la propriété");
                            SELF::$errorAds++;
                            return false;
                        }

                    }else{
                        return false;
                    }

                }else{
                    SELF::addLog("Impossible d'enregistrer l'image $imgURL dans le dossier 'uploads' pour l'annonce $propertyWPId (id BDD) erreur : ".$uploadThumbnail["error"]);
                    SELF::$errorAds++;
                    return false;
                }
            }else{
                SELF::addLog("Impossible de récupérer l'image à l'adresse $imgURL erreur : ".wp_remote_retrieve_response_code($img));
                SELF::$errorAds++;
                return false;
            }
        }

    }
    
    private static function CSVToArrayCleaned($importPath) {
        $optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");
        $optionsMapping = get_option(PLUGIN_RE_NAME."OptionsMapping");
        $dirSavesPath = ABSPATH.$optionsImports["dirSavesPath"];
        
        $zip = new ZipArchive;

        $fields = $optionsMapping; //On récupère les champs
        
        
        $dir = scandir($importPath); //Scan le dossier $importPath
        foreach($dir as $elem) {
            if(preg_match("/.zip$/", $elem)) { //S'il y a un .zip
                $zipName = $elem; //On garde son nom
                $CSVPath = $importPath."Annonces.csv";
                break; //On arrête la boucle
            }
        }
        if(!isset($zipName)) { //Si on a pas trouvé de zip
            SELF::addLog("Il n'y a pas de nouveau zip");
            SELF::$newAds = -1;
            return false;
        }

        if($zip->open($importPath.$zipName) === true) { //Ouvre le zip
            $zip->extractTo($importPath, "Annonces.csv"); //Extrait du fichier Annonces.csv vers le dossier $dirPath 
            $zip->close();
        }else{
            SELF::addLog("Impossible de dézipper le fichier $zipName");
            return false;
        }

        if(file_exists($CSVPath)) {
            if(($file = fopen($CSVPath, "r+")) !== false) { //Ouverture du fichier en lecture et écriture
                $fileContent = fread($file, filesize($CSVPath)); //Lit le contenu du fichier
                $fileContent = str_replace("!#", ";", $fileContent); //Remplace le délimiteur #! par ;
                $fileContent = str_getcsv($fileContent, "\n"); //Crée un tableau en délimitant chaque annonce
                if(!mb_check_encoding($fileContent, "UTF-8")) {
                    $fileContent = array_map("utf8_encode", $fileContent); //encode en utf8
                }
                $adsCleaned = array();
                foreach($fileContent as $ad) { //Pour chaque annonce
                    $adCleaned = array();
                    $ad = str_getcsv($ad, ";"); //Crée un tableau en délimitant chaque champ
                    foreach($fields as $field) { //Pour chaque champ
                        $key = $field["name"]; //La clé est le nom du champ
                        $value = $ad[intval($field["id"])-1]; //La valeur est l'élément de l'annonce indexée id du champ -1
                        $adCleaned[$key] = $value; //Le nouveau tableau contient uniquement les différents champs avec leur valeur associée pour chaque annonce
                    }
                    array_push($adsCleaned, $adCleaned); //On ajoute cette nouvelle annonce aux autres annonces
                }

                if(!is_dir($dirSavesPath)) { //Si le dossier de sauvegardes n'existe pas déjà
                    mkdir($dirSavesPath);
                }

                SELF::limitNumberSaves(); //On limite le nombre de sauvegardes

                if(!rename($importPath.$zipName, $dirSavesPath.date("d-m-Y_H\hi\ms\s").'_'.$zipName)) {
                    SELF::addLog("Impossible de déplacer le zip $zipName vers le dossier de sauvegardes");
                    //Pas bloquant
                }
                
                if(!unlink($CSVPath)) {
                    SELF::addLog("Impossible de supprimer le fichier ".$CSVPath);
                }

                fclose($file);
                return $adsCleaned;
            }else{
                SELF::addLog("Impossible d'ouvrir le fichier $CSVPath");
                return false;
            }
        }else{
            SELF::addLog("Le fichier $CSVPath n'existe pas");
            return false;
        }
    }
    
    public function startImport($publish = true) {
        $optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");
        $optionsMapping = get_option(PLUGIN_RE_NAME."OptionsMapping");
        $fields = $optionsMapping;
        $dirPath = ABSPATH."/".PLUGIN_RE_NAME."/";
        //Pour désactiver la limite d'exécution (pour le téléchargement et la conversion des images)
        set_time_limit(0);
        do {
            $arrayCleaned = SELF::CSVToArrayCleaned(ABSPATH.$optionsImports["dirImportPath"]);
            if($arrayCleaned !== false) { //Il y a un fichier zip et on a réussi à le dézipper

                //Pour chaque annonce
                foreach($arrayCleaned as $ad){

                    //On cherche s'il n'y a pas déjà une annonce avec le même ID
                    $args = array(
                        "numberposts" 	=> 1,
                        "post_type" 	=> "ad",
                        "meta_query" 	=> array(
                            array(
                                "key"       => "adUniqId",
                                "value"     => $ad["uniqId"],
                                "compare"   => "="
                            )
                        )
                    );
                    $action = "[création]"; //String pour afficher directement dans les logs
                    $property = get_posts($args); //A remplacer par get_post ?

                    if(!empty($property)) { //S'il existe déjà une propriété avec cet ID
                        $action = "[modification]"; 
                        $propertyWPId = $property[0]->ID;
                        if($property[0]->post_title !== $ad["title"] || $property[0]->post_content !== $ad["description"]) { //S'il y a un nouveau titre ou une nouvelle description
                            $post = 
                                array(
                                    "ID" => $propertyWPId,
                                    "post_date_gmt"	=> gmdate("Y-m-d H:i:s"),
                                    "post_title" 	=> $ad["title"],
                                    "post_content" 	=> $ad["description"]
                                );
                            if($publish) {//Si l'utilisateur a décidé publier directement les annonces
                                $post["post_status"] = "publish";
                            }else{
                                $post["post_status"] = "private";
                            }
                            $propertyWPId = wp_update_post($post, true);
                        }
                    }else{		
                        $post = array( //Array création du post
                            "post_title" 	=> $ad["title"],
                            "post_author" 	=> 1,
                            "post_content" 	=> $ad["description"],
                            "post_type" 	=> "ad",
                        );
                        if($publish) {//Si l'utilisateur a décidé publier directement les annonces
                            $post["post_status"] = "publish";
                        }else{
                            $post["post_status"] = "private";
                        }
                        $propertyWPId = wp_insert_post($post, true); //On crée la propriété et on obtient l'ID
                    }

                    if(!SELF::checkError($propertyWPId, "$action impossible de l'annonce ".$ad["uniqId"], true)) { //Si on a bien réussi à créer la propriété ou à la récupérer...
                        //On complète avec les terms et meta
                        
                        SELF::setPicturesProperty($propertyWPId, $ad["thumbnail"], true); //Pour la miniature
                        for($i=1; $i<=8; $i++) { //Les images
                            if(!empty($ad["picture".$i])) {
                                SELF::setPicturesProperty($propertyWPId, $ad["picture".$i]);
                            }
                        }

                        //Pour récupérer l'agent
                        if($agent = get_posts(array(
                            "numberposts"   => 1,
                            "post_type"     => "agent",
                            "meta_key"      => "agentEmail",
                            "meta_value"    => $ad["agentEmail"]
                        ))) {
                            SELF::checkError(update_post_meta($propertyWPId, "adIdAgent", $agent[0]->ID), "$action impossible de l'identifiant agent pour l'annonce ". $ad["uniqId"], true);
                        }
                        SELF::checkError(update_post_meta($propertyWPId, "adShowAgent", "on"), "$action impossible du statut d'affichage de l'agent pour l'annonce ". $ad["uniqId"], true); //A modifier selon un paramètre dans les options

                    
                        $location = get_post_meta($propertyWPId, "adAddress", true);
                        if(!$location || $location !== $ad["address"]) { //S'il n'y a pas déjà une adresse ou que l'ancienne adresse est différente de la nouvelle
                            //Visiblement il y a toujours un espace à la fin de l'adresse pour Hektor
                            $address = $ad["address"];
                            if(ctype_space(substr($address, -1))) {
                                $address = substr($address, 0, -1);
                            }
                            //Si on veut seulement la ville ou l'adresse complète A rajouter dans les options d'importation
                            if($optionsImports["addressPrecision"] === "all") {
                                $query = $address." ".$ad["postalCode"]." ".$ad["city"];
                                $url = "https://api-adresse.data.gouv.fr/search/?q=".$query."&limit=1"; 
                            }else{
                                $query = $ad["postalCode"]." ".$ad["city"];
                                $url = "https://api-adresse.data.gouv.fr/search/?q=".$query."&type=municipality&limit=1"; 
                            }
                            $apiResponse = json_decode(wp_remote_retrieve_body(wp_remote_get($url)), true);
                            
                            if(isset($apiResponse["features"][0])) { //Si on arrive à récupérer des infos à partir de l'adresse
                                $coordGPS = $apiResponse["features"][0]["geometry"]["coordinates"];
                                SELF::checkError(update_post_meta($propertyWPId, "adDataMap", array("lat"=>$coordGPS[1], "long"=>$coordGPS[0], "zoom"=>14, "circ"=>50)), "$action impossible des coordonnées GPS pour l'annonce ".$ad["uniqId"]);
                            }else{
                                update_post_meta($propertyWPId, "adDataMap", '');
                            }
                            SELF::checkError(update_post_meta($propertyWPId, "adAddress", $query), "$action impossible de l'adresse pour l'annonce ". $ad["uniqId"]);
                        }
                        
                        switch($ad["typeHeating"]) { //Rajouter collectif ou non
                            case 512:
                            case 640:
                            case 768:
                            case 896:
                            case 8704:
                            case 8832:
                            case 8960:
                            case 9088:
                                $typeHeating = 8704; //Individuel gaz
                                break;
                            case 4608:
                            case 4736:
                            case 4864:
                            case 4992:
                                $typeHeating = 4608; //Collectif gaz
                                break;
                            case 1024:
                            case 1152:
                            case 1280:
                            case 1408:
                            case 9216:
                            case 9344:
                            case 9472:
                            case 9600:
                                $typeHeating = 9216; //Individuel fuel
                                break;
                            case 5120:
                            case 5248:
                            case 5376:
                            case 5504:                  
                                $typeHeating = 5120; //Collectif fuel
                                break;
                            case 2048:
                            case 2176:
                            case 2304:
                            case 2432:
                            case 10240:
                            case 10368:
                            case 10496:
                            case 10624:
                                $typeHeating = 10240; //Individuel électrique
                                break;
                            case 6144:
                            case 6272:
                            case 6400:
                            case 6528:
                                $typeHeating = 6144; //Collectif électrique
                                break;
                            default:
                                $typeHeating = 0;
                                break;
                        }
                        SELF::checkError(update_post_meta($propertyWPId, "adTypeHeating", $typeHeating), "$action du type de chauffage pour l'annonce ".$ad["uniqId"]);                        
                        
                        SELF::checkError(wp_set_post_terms($propertyWPId, $ad["typeProperty"], "adTypeProperty"), "$action du type de bien pour l'annonce ".$ad["uniqId"]);
                        
                        SELF::checkError(wp_set_post_terms($propertyWPId, $ad["typeAd"], "adTypeAd"), "$action du type de l'annonce ".$ad["uniqId"]);
                        
                        SELF::checkError(wp_set_post_terms($propertyWPId, "Disponible", "adAvailable"), "$action de la disponibilté du bien de l'annonce ".$ad["uniqId"]);

                        $fieldsToNotUpdate = ["title", "description", "typeAd", "typeProperty", "thumbnail", "picture1", "picture2", "picture3", "picture4", "picture5", "picture6", "picture7", "picture8", "agentEmail", "address", "typeHeating", "feesAgency"];
                        $checkMeta = true;
                        foreach($fields as $field) {
                            if(!in_array($field["name"], $fieldsToNotUpdate)) { //On vérifie que le champ a bien besoin d'être enregistré
                                if(is_wp_error(update_post_meta($propertyWPId, "ad".ucfirst($field["name"]), $ad[$field["name"]]))) { //On vérifie s'il y a une erreur lors de l'update
                                    $checkMeta = false;
                                }
                            }
                        }

                        if($checkMeta) { //Si on a bien réussi à ajouter les terms et meta
                            //Pour l'affichage sur la page de paramétrage
                            if($action === "[création]") {
                                SELF::$newAds++;
                            }else{
                                SELF::$updatedAds++;
                            }
                        }else{
                            SELF::addLog("$action impossible de certaines metadonnées brutes pour l'annonce ".$ad["uniqId"]);
                        }
                    }
                }
                if(SELF::$sendMail) {
                    SELF::sendMail();
                }

            }else{
                break; //On arrête la boucle
            }
        }while(!empty(glob($dirPath.'*.{zip}', GLOB_BRACE))); //Tant qu'il reste des zip dans le dossier, on importe
        //remettre la limite de temps d'exécution
        set_time_limit(300);
    }   
    
    
    private static function checkError($test, $message=false, $sendMail=false) { //Retourne true si erreur
        $optionsEmail = get_option(PLUGIN_RE_NAME."OptionsEmail");
        if(is_wp_error($test)) {
            if(is_string($message)) {
                SELF::addLog($message);
            }
            if($sendMail && $optionsEmail["sendMail"] === "on") {
                SELF::$sendMail = true;
            }
            SELF::$errorAds++;
            return true;
        }
        return false;
    }
    
    private static function addLog($message) { 
        $date = date("d-m-Y_H\hi\ms\s");
        $messageLog = "[$date] $message".PHP_EOL; //Date et le message
        if(!file_exists(plugin_dir_path(__DIR__)."logsImport.txt")) {
            $messageLog = "\xEF\xBB\xBF".$messageLog; //Header UTF-8
        }
        $fileLog = fopen(plugin_dir_path(__DIR__)."logsImport.txt", "ab"); //Crée le fichier s'il n'existe pas. Ecriture seulement
        fwrite($fileLog, $messageLog); 
        fclose($fileLog);
    }
    
    //Pour supprimer le fichier de log
    function deleteLogs() {
        unlink(plugin_dir_path(__DIR__)."logsImport.txt");
    }
    
    private static function sendMail() {
        $optionsEmail = get_option(PLUGIN_RE_NAME."OptionsEmail");
        $logsURL = get_site_url()."/wp-content/plugins/".PLUGIN_RE_NAME."/logsImport.txt";
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $message = "Bonjour,<br />il y a eu une erreur lors de l'importation des annonces vers WordPress.<br />";
        $message .= '<br />Vous pouvez consulter les logs <a href="'.$logsURL.'">ici</a>.';
        if(!wp_mail($optionsEmail["email"], "Plugin ".PLUGIN_RE_NAME." : Erreur importation", $message, $headers)) {
            SELF::addLog("Impossible d'envoyer le mail de rapport d'erreur");
        }
    }
    
   
}

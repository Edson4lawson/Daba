<?php
/**
 * Sécurité des Uploads de Fichiers - Daba
 * Validation stricte des fichiers uploadés
 * 
 * @author Security Audit
 * @version 1.0.0
 */

/**
 * Extensions autorisées (whitelist stricte)
 */
$ALLOWED_EXTENSIONS = [
    'jpg', 'jpeg', 'png', 'gif', 'webp', // Images
    'pdf', // Documents
    'doc', 'docx', 'xls', 'xlsx', // Microsoft Office
    'csv' // Données
];

/**
 * MIME types autorisés
 */
$ALLOWED_MIME_TYPES = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'text/csv'
];

/**
 * Taille maximale (en octets)
 */
$MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB

/**
 * Dimensions maximales pour les images
 */
$MAX_IMAGE_WIDTH = 4096;
$MAX_IMAGE_HEIGHT = 4096;

/**
 * Valide un fichier uploadé
 * 
 * @param array $file Données du fichier ($_FILES)
 * @return array Résultat de la validation
 */
function validateUploadedFile($file) {
    global $ALLOWED_EXTENSIONS, $ALLOWED_MIME_TYPES, $MAX_FILE_SIZE;
    
    $errors = [];
    
    // Vérifier si le fichier existe
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['valid' => false, 'errors' => ['Aucun fichier uploadé']];
    }
    
    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = getUploadErrorMessage($file['error']);
        return ['valid' => false, 'errors' => $errors];
    }
    
    // Vérifier la taille
    if ($file['size'] > $MAX_FILE_SIZE) {
        $errors[] = 'Le fichier dépasse la taille maximale de ' . ($MAX_FILE_SIZE / 1024 / 1024) . ' MB';
    }
    
    // Vérifier l'extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $ALLOWED_EXTENSIONS)) {
        $errors[] = 'Extension de fichier non autorisée. Extensions autorisées: ' . implode(', ', $ALLOWED_EXTENSIONS);
    }
    
    // Vérifier le MIME type réel
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!in_array($mimeType, $ALLOWED_MIME_TYPES)) {
        $errors[] = 'Type MIME non autorisé: ' . $mimeType;
    }
    
    // Vérifier la cohérence extension/MIME
    if (!validateExtensionMimeMatch($extension, $mimeType)) {
        $errors[] = 'Incohérence entre l\'extension et le type MIME';
    }
    
    // Vérifier si c'est une image
    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $imageInfo = @getimagesize($file['tmp_name']);
        
        if ($imageInfo === false) {
            $errors[] = 'Le fichier n\'est pas une image valide';
        } else {
            global $MAX_IMAGE_WIDTH, $MAX_IMAGE_HEIGHT;
            
            if ($imageInfo[0] > $MAX_IMAGE_WIDTH || $imageInfo[1] > $MAX_IMAGE_HEIGHT) {
                $errors[] = 'Dimensions d\'image trop grandes (max: ' . $MAX_IMAGE_WIDTH . 'x' . $MAX_IMAGE_HEIGHT . ')';
            }
        }
    }
    
    // Vérifier les signatures de fichiers (magic bytes)
    if (!validateFileSignature($file['tmp_name'], $extension)) {
        $errors[] = 'Signature de fichier invalide';
    }
    
    // Scanner les métadonnées potentiellement dangereuses
    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
        if (containsExifData($file['tmp_name'])) {
            // Supprimer les métadonnées EXIF
            stripExifData($file['tmp_name']);
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'mime_type' => $mimeType,
        'extension' => $extension
    ];
}

/**
 * Obtient le message d'erreur d'upload
 */
function getUploadErrorMessage($errorCode) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par PHP',
        UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire',
        UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
        UPLOAD_ERR_NO_FILE => 'Aucun fichier uploadé',
        UPLOAD_ERR_NO_TMP_DIR => 'Répertoire temporaire manquant',
        UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque',
        UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload'
    ];
    
    return $errors[$errorCode] ?? 'Erreur d\'upload inconnue';
}

/**
 * Valide la cohérence entre l'extension et le MIME type
 */
function validateExtensionMimeMatch($extension, $mimeType) {
    $mimeMap = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'csv' => 'text/csv'
    ];
    
    return isset($mimeMap[$extension]) && $mimeMap[$extension] === $mimeType;
}

/**
 * Valide la signature du fichier (magic bytes)
 */
function validateFileSignature($filePath, $extension) {
    $handle = fopen($filePath, 'rb');
    $bytes = fread($handle, 8);
    fclose($handle);
    
    $signatures = [
        'jpg' => "\xFF\xD8\xFF",
        'jpeg' => "\xFF\xD8\xFF",
        'png' => "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A",
        'gif' => 'GIF87a',
        'webp' => 'RIFF',
        'pdf' => '%PDF'
    ];
    
    if (!isset($signatures[$extension])) {
        return true; // Pas de signature connue, accepter
    }
    
    return strpos($bytes, $signatures[$extension]) === 0;
}

/**
 * Vérifie si le fichier contient des données EXIF
 */
function containsExifData($filePath) {
    // Pour les images JPEG
    if (@exif_read_data($filePath)) {
        return true;
    }
    return false;
}

/**
 * Supprime les métadonnées EXIF d'une image
 */
function stripExifData($filePath) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    if (in_array($extension, ['jpg', 'jpeg'])) {
        // Recréer l'image sans EXIF
        $image = imagecreatefromjpeg($filePath);
        if ($image) {
            imagejpeg($image, $filePath, 90);
            imagedestroy($image);
        }
    } elseif ($extension === 'png') {
        $image = imagecreatefrompng($filePath);
        if ($image) {
            imagepng($image, $filePath, 9);
            imagedestroy($image);
        }
    }
}

/**
 * Génère un nom de fichier sécurisé
 * 
 * @param string $originalName Nom original du fichier
 * @return string Nom sécurisé
 */
function generateSecureFileName($originalName) {
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $uuid = bin2hex(random_bytes(16));
    return $uuid . '.' . $extension;
}

/**
 * Sauvegarde un fichier de manière sécurisée
 * 
 * @param array $file Données du fichier
 * @param string $destination Répertoire de destination
 * @return array Résultat
 */
function saveFileSecurely($file, $destination) {
    $validation = validateUploadedFile($file);
    
    if (!$validation['valid']) {
        return ['success' => false, 'errors' => $validation['errors']];
    }
    
    // Créer le répertoire si nécessaire
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Générer un nom de fichier sécurisé
    $secureName = generateSecureFileName($file['name']);
    $destinationPath = rtrim($destination, '/') . '/' . $secureName;
    
    // Déplacer le fichier
    if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
        return ['success' => false, 'errors' => ['Échec du déplacement du fichier']];
    }
    
    // Changer les permissions
    chmod($destinationPath, 0644);
    
    return [
        'success' => true,
        'filename' => $secureName,
        'path' => $destinationPath,
        'mime_type' => $validation['mime_type'],
        'size' => $file['size']
    ];
}

/**
 * Supprime un fichier de manière sécurisée
 * 
 * @param string $filePath Chemin du fichier
 * @return bool
 */
function deleteFileSecurely($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    // Vérifier que le fichier est dans un répertoire autorisé
    $allowedDirs = [
        __DIR__ . '/../uploads/',
        __DIR__ . '/../public/images/'
    ];
    
    $realPath = realpath($filePath);
    $isAllowed = false;
    
    foreach ($allowedDirs as $dir) {
        if (strpos($realPath, realpath($dir)) === 0) {
            $isAllowed = true;
            break;
        }
    }
    
    if (!$isAllowed) {
        error_log('Tentative de suppression de fichier non autorisé: ' . $filePath);
        return false;
    }
    
    return unlink($filePath);
}

<?php
/**
 * Middleware RBAC (Role-Based Access Control) - Bloom Chloé
 * Vérifie les permissions granulaires des utilisateurs
 * 
 * @author Security Audit
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 * 
 * @param array $user Données utilisateur
 * @param array|string $roles Rôle(s) à vérifier
 * @return bool True si l'utilisateur a l'un des rôles
 */
function hasRole($user, $roles) {
    if (is_string($roles)) {
        $roles = [$roles];
    }
    return in_array($user['role'] ?? '', $roles, true);
}

/**
 * Vérifie si l'utilisateur a une permission spécifique
 * 
 * @param array $user Données utilisateur
 * @param string $permission Nom de la permission (ex: 'products.update')
 * @return bool True si autorisé
 */
function hasPermission($user, $permission) {
    global $pdo;
    
    // Les super admins ont toutes les permissions
    if ($user['role'] === 'super_admin') {
        return true;
    }
    
    // Récupérer le role_id
    $roleId = $user['role_id'] ?? null;
    
    if (!$roleId) {
        // Fallback pour l'ancien système de rôles
        $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = ?');
        $stmt->execute([$user['role']]);
        $role = $stmt->fetch();
        
        if (!$role) {
            return false;
        }
        
        $roleId = $role['id'];
    }
    
    // Vérifier si la permission est assignée au rôle
    $stmt = $pdo->prepare('
        SELECT COUNT(*) as has_permission
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.id
        WHERE rp.role_id = ? AND p.name = ?
    ');
    $stmt->execute([$roleId, $permission]);
    $result = $stmt->fetch();
    
    return (int)$result['has_permission'] > 0;
}

/**
 * Vérifie si l'utilisateur a l'une des permissions requises
 * 
 * @param array $user Données utilisateur
 * @param array $permissions Liste des permissions
 * @return bool True si autorisé pour au moins une
 */
function hasAnyPermission($user, $permissions) {
    foreach ($permissions as $permission) {
        if (hasPermission($user, $permission)) {
            return true;
        }
    }
    return false;
}

/**
 * Vérifie si l'utilisateur a toutes les permissions requises
 * 
 * @param array $user Données utilisateur
 * @param array $permissions Liste des permissions
 * @return bool True si autorisé pour toutes
 */
function hasAllPermissions($user, $permissions) {
    foreach ($permissions as $permission) {
        if (!hasPermission($user, $permission)) {
            return false;
        }
    }
    return true;
}

/**
 * Exige une permission spécifique (renvoie 403 si non autorisé)
 * 
 * @param array $user Données utilisateur
 * @param string $permission Nom de la permission
 */
function requirePermission($user, $permission) {
    if (!hasPermission($user, $permission)) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Permission refusée',
            'required_permission' => $permission
        ]);
        exit();
    }
}

/**
 * Exige l'une des permissions requises
 * 
 * @param array $user Données utilisateur
 * @param array $permissions Liste des permissions
 */
function requireAnyPermission($user, $permissions) {
    if (!hasAnyPermission($user, $permissions)) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Permission refusée',
            'required_permissions' => $permissions
        ]);
        exit();
    }
}

/**
 * Vérifie si l'utilisateur a un niveau de rôle suffisant
 * 
 * @param array $user Données utilisateur
 * @param int $minLevel Niveau minimum requis
 * @return bool True si autorisé
 */
function hasRoleLevel($user, $minLevel) {
    global $pdo;
    
    $roleId = $user['role_id'] ?? null;
    
    if (!$roleId) {
        $stmt = $pdo->prepare('SELECT id, level FROM roles WHERE name = ?');
        $stmt->execute([$user['role']]);
        $role = $stmt->fetch();
        
        if (!$role) {
            return false;
        }
        
        return $role['level'] >= $minLevel;
    }
    
    $stmt = $pdo->prepare('SELECT level FROM roles WHERE id = ?');
    $stmt->execute([$roleId]);
    $role = $stmt->fetch();
    
    if (!$role) {
        return false;
    }
    
    return $role['level'] >= $minLevel;
}

/**
 * Protection IDOR (Insecure Direct Object Reference)
 * Vérifie que l'utilisateur a le droit d'accéder à la ressource
 * 
 * @param array $user Données utilisateur
 * @param string $resourceType Type de ressource (order, product, user, etc.)
 * @param int $resourceId ID de la ressource
 * @return bool True si autorisé
 */
function checkResourceOwnership($user, $resourceType, $resourceId) {
    global $pdo;
    
    // Les admins et super admins peuvent accéder à toutes les ressources
    if (in_array($user['role'], ['admin', 'super_admin'])) {
        return true;
    }
    
    // Vérifier selon le type de ressource
    switch ($resourceType) {
        case 'order':
            $stmt = $pdo->prepare('SELECT user_id FROM orders WHERE id = ?');
            $stmt->execute([$resourceId]);
            $resource = $stmt->fetch();
            return $resource && $resource['user_id'] == $user['id'];
            
        case 'cart':
            $stmt = $pdo->prepare('SELECT user_id FROM cart WHERE id = ?');
            $stmt->execute([$resourceId]);
            $resource = $stmt->fetch();
            return $resource && $resource['user_id'] == $user['id'];
            
        case 'favorite':
            $stmt = $pdo->prepare('SELECT user_id FROM favorites WHERE id = ?');
            $stmt->execute([$resourceId]);
            $resource = $stmt->fetch();
            return $resource && $resource['user_id'] == $user['id'];
            
        default:
            // Par défaut, refuser l'accès
            return false;
    }
}

/**
 * Exige la propriété de la ressource (protection IDOR)
 * 
 * @param array $user Données utilisateur
 * @param string $resourceType Type de ressource
 * @param int $resourceId ID de la ressource
 */
function requireResourceOwnership($user, $resourceType, $resourceId) {
    if (!checkResourceOwnership($user, $resourceType, $resourceId)) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Accès non autorisé à cette ressource'
        ]);
        exit();
    }
}

/**
 * Récupère toutes les permissions d'un utilisateur
 * 
 * @param array $user Données utilisateur
 * @return array Liste des permissions
 */
function getUserPermissions($user) {
    global $pdo;
    
    // Les super admins ont toutes les permissions
    if ($user['role'] === 'super_admin') {
        $stmt = $pdo->query('SELECT name FROM permissions');
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
    }
    
    $roleId = $user['role_id'] ?? null;
    
    if (!$roleId) {
        $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = ?');
        $stmt->execute([$user['role']]);
        $role = $stmt->fetch();
        
        if (!$role) {
            return [];
        }
        
        $roleId = $role['id'];
    }
    
    $stmt = $pdo->prepare('
        SELECT p.name 
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.id
        WHERE rp.role_id = ?
    ');
    $stmt->execute([$roleId]);
    
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
}

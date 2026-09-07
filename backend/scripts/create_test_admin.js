// Script de test pour créer un utilisateur admin temporaire
// Exécuter dans la console du navigateur sur http://localhost:5174

localStorage.setItem('access_token', 'test_admin_token');
localStorage.setItem('refresh_token', 'test_refresh_token');
localStorage.setItem('user', JSON.stringify({
  id: 1,
  email: 'admin@daba.tg',
  first_name: 'Admin',
  last_name: 'User',
  role: 'admin'
}));

console.log('Utilisateur admin temporaire créé !');
console.log('Redirection vers /admin/dashboard...');
window.location.href = '/admin/dashboard';

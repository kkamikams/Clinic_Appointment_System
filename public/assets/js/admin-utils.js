// Minimal admin utils placeholder
// Added to prevent 404 and allow page scripts to run.
(function(){
  if (typeof window.__QA_ADMIN_UTILS_LOADED__ === 'undefined') {
    window.__QA_ADMIN_UTILS_LOADED__ = true;
    // small helper: safe JSON parse
    window.safeJsonParse = function(text){ try { return JSON.parse(text); } catch(e) { return null; } };
    console.log('admin-utils loaded');
  }
})();

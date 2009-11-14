jQuery.extend(jQuery.expr[':'],{
  containsIgnoreCase: "(a.textContent||a.innerText||jQuery(a).text()||'').toLowerCase().indexOf((m[3]||'').toLowerCase())>=0"
});

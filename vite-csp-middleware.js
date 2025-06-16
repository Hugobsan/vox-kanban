// Middleware customizado para remover CSP do Vite
export function removeCSPMiddleware() {
  return {
    name: 'remove-csp',
    configureServer(server) {
      server.middlewares.use((req, res, next) => {
        // Interceptar a resposta para remover CSP headers
        const originalSetHeader = res.setHeader;
        res.setHeader = function(name, value) {
          // Bloquear headers CSP
          if (name.toLowerCase().includes('content-security-policy') || 
              name.toLowerCase().includes('csp')) {
            console.log(`Blocking CSP header: ${name}: ${value}`);
            return;
          }
          return originalSetHeader.call(this, name, value);
        };
        
        // Interceptar a resposta para modificar CSP
        const originalWrite = res.write;
        const originalEnd = res.end;
        
        res.write = function(chunk, encoding) {
          // Se for HTML, remover qualquer CSP meta tag
          if (typeof chunk === 'string' && chunk.includes('<meta')) {
            chunk = chunk.replace(/<meta[^>]*content-security-policy[^>]*>/gi, '');
          }
          return originalWrite.call(this, chunk, encoding);
        };
        
        res.end = function(chunk, encoding) {
          if (typeof chunk === 'string' && chunk.includes('<meta')) {
            chunk = chunk.replace(/<meta[^>]*content-security-policy[^>]*>/gi, '');
          }
          return originalEnd.call(this, chunk, encoding);
        };
        
        next();
      });
    }
  };
}

/**
 * Componente de login con logo y pestañas de rol, todo en español.
 */
function LoginForm() {
  const [role, setRole] = React.useState("Estudiante");

  return (
        <div className="login-container">
      <img src="/images/SAVESA_LOGO.png" alt="Logo SAVESA" className="login-logo" />
      <h2>Iniciar Sesión</h2>
      <p>Accede a tu cuenta</p>

      <div className="tabs">
        {["Estudiante", "Profesor", "Administrador"].map((r) => (
          <div
            key={r}
            className={`tab ${role === r ? "active" : ""}`}
            onClick={() => setRole(r)}
          >
            {r}
          </div>
        ))}
      </div>

      <form method="POST" action="procesar_login.php">
        <input type="hidden" name="role" value={role} />

        <label htmlFor="email">Correo electrónico</label>
        <input
          type="email"
          id="email"
          name="email"
          required
          placeholder="Ingresa tu correo"
        />

        <label htmlFor="password">Contraseña</label>
        <input
          type="password"
          id="password"
          name="password"
          required
          placeholder="Ingresa tu contraseña"
        />

        <button type="submit">Entrar</button>
      </form>

      <div className="login-links">
    <a href="#" className="login-link">¿Olvidaste tu contraseña?</a>
    <a href="/pages/register.html" className="login-link">Registrarse</a>
  </div>


    </div>
  );
}

// Montamos el componente en el DOM
const root = ReactDOM.createRoot(document.getElementById("root"));
root.render(<LoginForm />);

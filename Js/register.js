function RegisterForm() {
  const [role, setRole] = React.useState("Estudiante");
  const [form, setForm] = React.useState({
    fullName: "",
    email: "",
    idCard: "",
    password: "",
    confirmPassword: "",
  });
  const [error, setError] = React.useState("");

  function handleInputChange(e) {
    setForm({
      ...form,
      [e.target.name]: e.target.value,
    });
  }

  function handleSubmit(e) {
    if (form.password !== form.confirmPassword) {
      e.preventDefault();
      setError("Las contraseñas no coinciden.");
      return;
    }
    setError("");
    // Aquí puedes enviar el formulario si todo está bien
  }

  return (
    <div className="register-container">
      <img 
        src="/images/SAVESA_LOGO.png"
        alt="Logo de SAVESA" 
        className="register-logo"
      />
      <h2>Crear Cuenta</h2>
      <p className="subtitle">Selecciona tu rol</p>

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

      <form id="registerForm" className="register-form" method="POST" action="/api/register" onSubmit={handleSubmit}>
        <input type="hidden" name="role" value={role} />

        <label htmlFor="fullName">Nombre completo</label>
        <input
          type="text"
          id="fullName"
          name="fullName"
          placeholder="Ingresa tu nombre completo"
          required
          value={form.fullName}
          onChange={handleInputChange}
        />

        <label htmlFor="email">Correo electrónico</label>
        <input
          type="email"
          id="email"
          name="email"
          placeholder="Ingresa tu correo electrónico"
          required
          value={form.email}
          onChange={handleInputChange}
        />

        <label htmlFor="idCard">Número de ID / Carnet</label>
        <input
          type="text"
          id="idCard"
          name="idCard"
          placeholder="Ingresa tu número de ID o carnet"
          required
          value={form.idCard}
          onChange={handleInputChange}
        />

        <label htmlFor="password">Contraseña</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Crea una contraseña"
          required
          value={form.password}
          onChange={handleInputChange}
        />

        <label htmlFor="confirmPassword">Confirmar contraseña</label>
        <input
          type="password"
          id="confirmPassword"
          name="confirmPassword"
          placeholder="Repite tu contraseña"
          required
          value={form.confirmPassword}
          onChange={handleInputChange}
        />

        {error && <div className="error">{error}</div>}

        <button type="submit" className="btn">Registrarse</button>
      </form>

        <p className="login-link">
      ¿Ya tienes una cuenta? <a href="login.html">Iniciar sesión</a>
    </p>

    </div>
  );
}

// Montaje en el DOM:
const root = ReactDOM.createRoot(document.getElementById("root"));
root.render(<RegisterForm />);

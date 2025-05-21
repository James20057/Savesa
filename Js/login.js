/**
 * Componente de login con pestañas de rol.
 */
function LoginForm() {
  const [role, setRole] = React.useState("Student");

  return (
    <div className="login-container">
      <h2>Sign In</h2>
      <p>Access your account</p>

      <div className="tabs">
        {["Student", "Teacher", "Administrator"].map((r) => (
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

        <label htmlFor="email">Email</label>
        <input
          type="email"
          id="email"
          name="email"
          required
          placeholder="Enter your email"
        />

        <label htmlFor="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          required
          placeholder="Enter your password"
        />

        <button type="submit">Login</button>
      </form>

      <div className="forgot">
        <a href="#">Forgot password?</a>
      </div>
    </div>
  );
}

// Montamos el componente en el DOM
const root = ReactDOM.createRoot(document.getElementById("root"));
root.render(<LoginForm />);

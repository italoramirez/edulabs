import API from "../api.js";
import dashboardPage from "./dashboard.js";

export default function loginPage(root) {
    root.innerHTML = `
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
      <div class="bg-white p-8 rounded-xl shadow-md w-96">
        <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">Iniciar sesión</h1>
        <form id="login-form" class="space-y-4">
          <input type="email" id="email" placeholder="Correo" class="w-full border p-2 rounded-lg" required />
          <input type="password" id="password" placeholder="Contraseña" class="w-full border p-2 rounded-lg" required />
          <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Entrar</button>
        </form>
        <p id="error" class="text-red-500 text-sm mt-3 hidden">Credenciales inválidas</p>
      </div>
    </div>
  `;

    const form = document.querySelector("#login-form");
    const error = document.querySelector("#error");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        error.classList.add("hidden");

        const email = document.querySelector("#email").value;
        const password = document.querySelector("#password").value;

        try {
            await API.login(email, password);
            localStorage.setItem("auth", "true");
            dashboardPage(root);
        } catch {
            error.classList.remove("hidden");
        }
    });
}

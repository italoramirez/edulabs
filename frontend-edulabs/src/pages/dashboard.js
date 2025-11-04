import API from "../api.js";
import loginPage from "./login.js";

export default async function dashboardPage(root) {
    const user = await API.getCurrentUser();
    const isAdmin = user.role === "admin";

    root.innerHTML = `
    <div class="min-h-screen bg-gray-50 p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📁 Panel de almacenamiento</h1>
        <div class="flex items-center gap-4">
          <span class="text-gray-600">👋 ${user.name} (${user.role})</span>
          <button id="logout" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Cerrar sesión</button>
        </div>
      </div>

      ${
        isAdmin
            ? `
        <div class="mb-8">
          <nav class="flex gap-3 border-b pb-2">
            <button id="tab-files" class="tab-btn active-tab">Archivos</button>
            <button id="tab-users" class="tab-btn">Usuarios</button>
            <button id="tab-groups" class="tab-btn">Grupos</button>
            <button id="tab-extensions" class="tab-btn">Validaciones</button>
          </nav>
        </div>
        <div id="admin-content"></div>
        `
            : `
        <div>
          <h2 class="text-lg font-semibold mb-3">Archivos</h2>
          <div id="user-content"></div>
        </div>
        `
    }
    </div>
  `

    const logoutBtn = document.querySelector("#logout");
    logoutBtn.addEventListener("click", async () => {
        await API.logout()
        localStorage.removeItem("auth")
        loginPage(root)
    })

    if (isAdmin) {
        const content = document.querySelector("#admin-content");

        document.querySelector("#tab-files").addEventListener("click", renderFilesTab)
        document.querySelector("#tab-users").addEventListener("click", renderUsersTab)
        document.querySelector("#tab-groups").addEventListener("click", renderGroupsTab)
        document.querySelector("#tab-extensions").addEventListener("click", renderExtensionsTab)

        await renderFilesTab();

        // 📂 Archivos
        async function renderFilesTab() {
            setActive("tab-files");
            content.innerHTML = `
            <div class="bg-white p-6 rounded-xl shadow">
              <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-lg p-6 cursor-pointer hover:bg-gray-50 mb-4">
                <span id="upload-text" class="text-gray-500">Seleccionar archivo</span>
                <input type="file" id="file-input" class="hidden" />
              </label>
              <ul id="file-list" class="divide-y divide-gray-100 bg-white rounded-lg shadow overflow-hidden"></ul>
            </div>
          `

            const input = document.querySelector("#file-input");
            const text = document.querySelector("#upload-text");
            const list = document.querySelector("#file-list");

            input.addEventListener("change", async (e) => {
                const file = e.target.files[0];
                if (!file) return;
                text.textContent = "Subiendo..."
                await API.upload(file);
                await loadFiles();
                text.textContent = "Seleccionar archivo"
            });

            async function loadFiles() {
                const files = await API.getFiles()
                if (!files.length) {
                    list.innerHTML = `<li class="p-4 text-gray-500">No hay archivos.</li>`
                    return;
                }
                list.innerHTML = files
                    .map(
                        (f) => `
          <li class="flex justify-between items-center p-3 hover:bg-gray-50 transition">
            <a href="${f.url}" target="_blank" class="text-blue-600 hover:underline">${f.filename}</a>
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-500">${(f.size / 1024).toFixed(1)} KB</span>
              <button data-id="${f.id}" class="delete-btn bg-red-100 hover:bg-red-200 text-red-600 rounded-full w-8 h-8 flex items-center justify-center">🗑️</button>
            </div>
          </li>`
                    )
                    .join("");

                document.querySelectorAll(".delete-btn").forEach((btn) => {
                    btn.addEventListener("click", async (e) => {
                        const id = e.currentTarget.dataset.id;
                        if (confirm("¿Eliminar archivo?")) {
                            await API.deleteFile(id);
                            await loadFiles();
                        }
                    });
                });
            }
            await loadFiles();
        }

        async function renderExtensionsTab() {
            setActive("tab-extensions");
            content.innerHTML = `
            <div class="bg-white p-6 rounded-xl shadow space-y-6">
              <h2 class="text-lg font-semibold text-gray-800">⚙️ Configuración General</h2>
        
              <div>
                <p class="text-gray-600 text-sm mb-3">
                  Selecciona las extensiones que <strong>NO</strong> estarán permitidas:
                </p>
                <form id="ext-form" class="grid grid-cols-2 sm:grid-cols-3 gap-3"></form>
              </div>
        
              <div class="mt-6">
                <label for="default_limit" class="block text-sm font-medium text-gray-700 mb-1">
                  💾 Límite de almacenamiento global (MB)
                </label>
                <input 
                  type="number" 
                  id="default_limit" 
                  min="1" 
                  step="1" 
                  class="border rounded-lg px-3 py-2 w-48 focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                />
              </div>
        
              <button id="save-settings" class="mt-6 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                Guardar cambios
              </button>
            </div>
          `;

            const form = document.querySelector("#ext-form");
            const saveBtn = document.querySelector("#save-settings");
            const limitInput = document.querySelector("#default_limit");

            // Lista de extensiones que se mostrarán
            const allExtensions = [
                "pdf", "jpg", "jpeg", "png", "gif", "zip", "rar", "docx", "txt",
                "exe", "sh", "php", "js", "html"
            ];

            const settings = await API.getSettings();
            const forbidden = settings.forbidden_extensions || [];
            const defaultLimitMB = (settings.default_limit || 104857600) / 1048576;
            limitInput.value = defaultLimitMB;

            form.innerHTML = allExtensions
                .map((ext) => {
                    const fullExt = `.${ext}`;
                    const checked = forbidden.includes(fullExt) ? "checked" : "";
                    return `
        <label class="flex items-center gap-2 text-gray-700">
          <input type="checkbox" name="extensions" value="${fullExt}" ${checked}
            class="w-4 h-4 rounded border-gray-300">
          <span>${fullExt}</span>
        </label>
      `;
            })
            .join("");

            saveBtn.addEventListener("click", async (e) => {
                e.preventDefault();

                const selected = Array.from(
                    document.querySelectorAll('input[name="extensions"]:checked')
                ).map((cb) => cb.value);

                const limitBytes = parseInt(limitInput.value) * 1024 * 1024;

                const result = await API.updateSettings({
                    default_limit: limitBytes,
                    forbidden_extensions: selected,
                });

                if (result?.success) {
                    alert("✅ Configuración actualizada correctamente");
                } else {
                    alert("❌ Error al guardar configuración");
                }
            });
        }


        async function renderUsersTab() {
            setActive("tab-users");
            content.innerHTML = `
            <div class="bg-white p-6 rounded-xl shadow space-y-6">
              <h2 class="text-lg font-semibold">👥 Registro de Usuarios</h2>
              <form id="user-form" class="space-y-3">
                <input name="name" placeholder="Nombre" class="input" required />
                <input name="email" placeholder="Correo" type="email" class="input" required />
                <input name="password" placeholder="Contraseña" type="password" class="input" required />
                <input 
                  type="number" 
                  name="storage_limit"
                  min="1"
                  class="input"
                  placeholder="MB"
                />

                <select name="role" class="input">
                  <option value="user">Usuario</option>
                  <option value="admin">Administrador</option>
                </select>
                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Crear usuario</button>
              </form>
              <div>
                <h3 class="text-md font-semibold mb-2">Usuarios</h3>
                <ul id="user-list" class="space-y-1"></ul>
              </div>
            </div>
          `

            const form = document.querySelector("#user-form");
            const userListElement = document.querySelector("#user-list");

            async function loadUsers() {
                const users = await API.getUsers();
                userListElement.innerHTML = users
                    .map((u) => `
                        <div class="flex justify-between items-center pb-2">
                            <li class="text-gray-700 text-sm">👤 ${u.name} (${u.email}) ${u.role === 'admin' ? 'Administrador' : 'Usuario'}</li>
                        <div class="flex items-center gap-2">
                          <input 
                            type="number" 
                            name="storage_limit"
                            class="input"
                            value="${u.storage_limit ? u.storage_limit / 1024 / 1024 : ''}" 
                            placeholder="MB"
                            data-id="${u.id}"
                          />
                          <button 
                            class="update-user-limit bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 cursor-pointer"
                            data-id="${u.id}"
                          >
                            💾
                          </button>
                          <button data-id="${u.id}" class="delete-btn bg-red-100 hover:bg-red-200 text-red-600 rounded-full w-8 h-8 flex items-center justify-center cursor-pointer">🗑️</button>
                        </div>
                        </div>
                        `)
                    .join("");
            }

            userListElement.addEventListener("click", async (e) => {
                if (e.target.classList.contains("update-user-limit")) {
                    const btn = e.target;
                    const id = btn.dataset.id;
                    const input = btn.previousElementSibling;
                    const limitBytes = parseInt(input.value) * 1024 * 1024;

                    const res = await fetch(`${API.baseUrl}/users/${id}/limit`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "Authorization": `Bearer ${localStorage.getItem("token")}`,
                        },
                        body: JSON.stringify({ storage_limit: limitBytes }),
                    });

                    const data = await res.json();
                    alert(data.success ? "✅ Límite actualizado" : "❌ Error al actualizar");
                }
            })

            userListElement.addEventListener("click", async (e) => {
                if (e.target.classList.contains("delete-btn")) {
                    const btn = e.target
                    const id = btn.dataset.id
                    if (confirm("¿Eliminar usuario?")) {
                        await API.deleteUser(id);
                        await loadUsers()
                    }
                }
            })

            form.addEventListener("submit", async (e) => {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(form).entries());
                await API.createUser(data);
                await loadUsers();
                form.reset();
            });

            await loadUsers();
        }

        async function renderGroupsTab() {
            setActive("tab-groups");
                    content.innerHTML = `
            <div class="bg-white p-6 rounded-xl shadow space-y-6">
              <h2 class="text-lg font-semibold">👪 Gestión de Grupos</h2>
        
              <form id="group-form" class="space-y-3">
                <input name="name" placeholder="Nombre del grupo" class="input" required />
                <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Crear grupo</button>
              </form>
        
              <div>
                <h3 class="text-md font-semibold mb-2">📋 Grupos y usuarios</h3>
                <ul id="group-list" class="space-y-4"></ul>
              </div>
        
              <div class="mt-6">
                <h3 class="text-md font-semibold mb-2">🔗 Asignar usuario a grupo</h3>
                <form id="assign-form" class="flex flex-col gap-3">
                  <select name="user_id" id="user-select" class="input" required></select>
                  <select name="group_id" id="group-select" class="input" required></select>
                  <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">Asignar</button>
                </form>
              </div>
            </div>
          `;

            const groupListElement = document.querySelector("#group-list");
            const assignForm = document.querySelector("#assign-form");
            const groupForm = document.querySelector("#group-form");

            async function loadGroups() {
                const groups = await API.getGroups();
                groupListElement.innerHTML = groups
                    .map(
                        (g) => `
                  <li class="border rounded-lg p-4 bg-gray-50">
                    <h4 class="font-semibold text-gray-800 mb-2">📦 ${g.name}</h4>
                    ${
                                        g.users.length
                                            ? `<ul class="pl-4 space-y-1 text-sm text-gray-600">
                            ${g.users
                                                .map((u) => `<li>👤 ${u.name} <span class="text-gray-400">(${u.email})</span></li>`)
                                                .join("")}
                          </ul>`
                                            : `<p class="text-gray-400 text-sm italic">Sin usuarios asignados</p>`
                                    }
                  </li>`
                )
                .join("");
                await refreshSelects();
            }

            async function refreshSelects() {
                const [users, groups] = await Promise.all([API.getUsers(), API.getGroups()]);
                const userSelect = document.querySelector("#user-select");
                const groupSelect = document.querySelector("#group-select");

                userSelect.innerHTML = users.map((u) => `<option value="${u.id}">${u.name}</option>`).join("");
                groupSelect.innerHTML = groups.map((g) => `<option value="${g.id}">${g.name}</option>`).join("");
            }

            groupForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(e.target).entries());
                const res = await API.createGroup(data)
                if (res?.message?.includes("ya existe")) {
                    alert("⚠️ Este grupo ya existe, no se ha duplicado.");
                } else {
                    alert("✅ Grupo creado correctamente");
                }
                await loadGroups();
                e.target.reset();
            });

            assignForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(e.target).entries());
                const res = await API.assignUserToGroup(data);
                alert(res ? "✅ Usuario asignado" : "❌ Error al asignar");
                await loadGroups();
            });

            await loadGroups();
        }

        function setActive(id) {
            document.querySelectorAll(".tab-btn").forEach((b) => b.classList.remove("active-tab"));
            document.getElementById(id).classList.add("active-tab");
        }
    }

    if (!isAdmin) {
        const userContent = document.querySelector("#user-content");
        userContent.innerHTML = `
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-lg p-6 cursor-pointer hover:bg-gray-50">
          <span id="upload-text" class="text-gray-500">Seleccionar archivo</span>
          <input type="file" id="file-input" class="hidden" />
        </label>
        <ul id="file-list" class="divide-y divide-gray-100 bg-white rounded-lg shadow overflow-hidden"></ul>
      </div>
    `;

        const input = document.querySelector("#file-input");
        const text = document.querySelector("#upload-text");
        const list = document.querySelector("#file-list");

        input.addEventListener("change", async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            text.textContent = "Subiendo...";
            await API.upload(file);
            await loadFiles();
            text.textContent = "Seleccionar archivo";
        });

        async function loadFiles() {
            const files = await API.getFiles();
            list.innerHTML = files
                .map(
                    (f) => `
        <li class="p-3 flex justify-between items-center">
          <a href="${f.url}" target="_blank" class="text-blue-600 hover:underline">${f.filename}</a>
          <span class="text-gray-500 text-sm">${(f.size / 1024).toFixed(1)} KB</span>
        </li>`
                )
                .join("");
        }
        await loadFiles();
    }
}
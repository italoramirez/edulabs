const API = {
    baseUrl: import.meta.env.VITE_API_URL,

    async login(email, password) {
        try {
            const response = await fetch(`${this.baseUrl}/login`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                },
                body: JSON.stringify({email, password}),
            });

            const json = await response.json();

            if (response.ok) {
                localStorage.setItem('token', json.token)
                if (json.user) {
                    localStorage.setItem("user", JSON.stringify(json.user))
                }
                return json
            } else {
                throw new Error(json.message || "Credenciales inválidas");
            }
        } catch (e) {
            console.error(e);
            throw e
        }
    },
    async logout() {
        try {
            const response = await fetch(`${this.baseUrl}/logout`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                }
            })
            if (!response.ok) {
                localStorage.removeItem('token')
                localStorage.removeItem('user')
                localStorage.removeItem('auth', false)
            }
        } catch (e) {
            console.error(e)
        }
    },

    async getFiles() {
        try {
            const res = await fetch(`${this.baseUrl}/files`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": `Bearer ${localStorage.getItem("token")}`,
                },
            });

            if (!res.ok) throw new Error("Error al obtener archivos");
            const data = await res.json();
            return Array.isArray(data) ? data : [];
        } catch (e) {
            console.error("Error en getFiles:", e);
            return [];
        }
    },
    async renderFiles() {
        const list = document.querySelector("#file-list");
        list.innerHTML = `<p class="p-4 text-gray-500">Cargando archivos...</p>`;

        const files = await this.getFiles();

        if (!files || !files.length) {
            list.innerHTML = `<p class="p-4 text-gray-500">No hay archivos disponibles.</p>`;
            return;
        }

        list.innerHTML = files
            .map((f) => {
                const {icon, isImage} = this.getFileInfo(f);

                return `
      <li class="flex items-center justify-between bg-white p-3 rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 mb-2">
        <div class="flex items-center gap-3 overflow-hidden">
          <span class="text-2xl">${icon}</span>
          <div class="truncate">
            <a href="${f.url}" target="_blank" 
              class="font-medium text-blue-600 hover:underline truncate block">
              ${f.filename}
            </a>
            <p class="text-sm text-gray-500">${(f.size / 1024).toFixed(1)} KB</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          ${
                    isImage
                        ? `<img src="${f.url}" class="w-12 h-12 object-cover rounded-lg border" alt="preview">`
                        : ""
                }
          <button 
            class="delete-btn flex items-center justify-center w-8 h-8 rounded-full bg-red-100 hover:bg-red-200 text-red-600 transition"
            data-id="${f.id}"
            title="Eliminar archivo">
            🗑️
          </button>
        </div>
      </li>
      `;
            })
            .join("");

        document.querySelectorAll(".delete-btn").forEach((btn) => {
            btn.addEventListener("click", async (e) => {
                const id = e.currentTarget.dataset.id;
                const confirmed = confirm("¿Seguro que deseas eliminar este archivo?");
                if (confirmed) {
                    const deleted = await this.deleteFile(id);
                    if (deleted) {
                        this.renderFiles(); // 🔄 Actualizar lista
                    }
                }
            });
        });
    }
    ,
    getFileInfo(file) {
        const ext = file.filename.split(".").pop().toLowerCase();
        let icon = "📁";
        let isImage = false;

        switch (ext) {
            case "pdf":
                icon = "📄";
                break;
            case "jpg":
            case "jpeg":
            case "png":
            case "gif":
            case "webp":
                icon = "🖼️";
                isImage = true;
                break;
            case "zip":
            case "rar":
                icon = "🗜️";
                break;
            case "txt":
            case "md":
                icon = "📜";
                break;
            case "doc":
            case "docx":
                icon = "📘";
                break;
            default:
                icon = "📁";
        }

        return {icon, isImage, ext};
    },

    async upload(file) {
        try {
            const formData = new FormData();
            formData.append("file", file);

            const res = await fetch(`${this.baseUrl}/upload`, {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "Authorization": `Bearer ${localStorage.getItem("token")}`,
                },
                body: formData,
            });

            if (!res.ok) throw new Error("Error al subir archivo");

            const result = await res.json();

            await this.renderFiles();

            return result;
        } catch (e) {
            console.error("Error al subir archivo:", e);
        }
    },
    async deleteFile(id) {
        try {
            const res = await fetch(`${this.baseUrl}/files/${id}`, {
                method: "DELETE",
                headers: {
                    "Accept": "application/json",
                    "Authorization": `Bearer ${localStorage.getItem("token")}`,
                },
            });

            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.error || "Error al eliminar archivo");
            }

            const data = await res.json();
            console.log(data.message);
            return true;

        } catch (e) {
            console.error("Error eliminando archivo:", e.message);
            alert("No se pudo eliminar el archivo.");
            return false;
        }
    },
    async getCurrentUser() {
        const res = await fetch(`${this.baseUrl}/me`, {
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("token")}`,
                "Accept": "application/json",
            },
        });
        return await res.json();
    },
    async createUser(data) {
        const res = await fetch(`${this.baseUrl}/admin/register`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "Authorization": `Bearer ${localStorage.getItem("token")}`,
            },
            body: JSON.stringify(data),
        });
        return res.ok;
    },
    async getUsers() {
        const res = await fetch(`${this.baseUrl}/users`, {
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("token")}`,
                "Accept": "application/json",
            },
        });
        return res.ok ? await res.json() : [];
    },
    async getGroups() {
        const res = await fetch(`${this.baseUrl}/admin/groups`, {
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("token")}`,
                "Accept": "application/json",
            },
        });
        return res.ok ? await res.json() : [];
    },
    async createGroup(data) {
        try {
            const res = await fetch(`${this.baseUrl}/admin/groups`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${localStorage.getItem("token")}`,
                },
                body: JSON.stringify(data),
            });
            return res.ok;
        } catch (e) {
            console.error(e)
        }
    },
    async assignUserToGroup(data) {
        const res = await fetch(`${this.baseUrl}/admin/assign`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${localStorage.getItem("token")}`,
            },
            body: JSON.stringify(data),
        });
        return res.ok;
    },
    async getSettings() {
        try {
            const res = await fetch(`${this.baseUrl}/settings`, {
                headers: {
                    "Authorization": `Bearer ${localStorage.getItem("token")}`,
                    "Accept": "application/json",
                },
            });
            return await res.json()
        } catch (e) {
            console.error(e)
        }
    },
    async deleteUser(userId) {
        try {
            const res = await fetch(`${this.baseUrl}/users/${userId}`, {
                method: "DELETE",
                headers: {
                    "Authorization": `Bearer ${localStorage.getItem("token")}`,
                    "Accept": "application/json",
                },
            })
            if (res.ok) {
                return res.ok;
            }
        } catch (e) {
            console.error(e)
        }
    },
    async updateSettings(forbidden_extensions, default_limit) {
        const res = await fetch(`${this.baseUrl}/settings`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${localStorage.getItem("token")}`,
            },
            body: JSON.stringify({forbidden_extensions, default_limit}),
        });
        return await res.json();
    },

}

export default API;

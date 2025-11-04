import "./style.css"
import loginPage from "./pages/login.js";
import dashboardPage from "./pages/dashboard.js";

const root = document.getElementById("app");

if (localStorage.getItem("auth")) {
    dashboardPage(root);
} else {
    loginPage(root);
}

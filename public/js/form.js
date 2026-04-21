let currentStep = 0;
let steps = [];

document.addEventListener("DOMContentLoaded", () => {

    steps = document.querySelectorAll(".step");
    const form = document.getElementById("multiForm");

    if (!steps.length) {
        console.error("No hay steps");
        return;
    }

    function showStep(index) {
        steps.forEach((step, i) => {
            step.classList.toggle("active", i === index);
        });

        let percent = ((index + 1) / steps.length) * 100;
        document.getElementById("progressBar").style.width = percent + "%";
    }
 // ======================
    // GUARDAR EN LOCALSTORAGE
    // ======================
    function saveData() {

        let data = {};

        form.querySelectorAll("input, select, textarea").forEach(el => {

            if (!el.name) return;

            if (el.type === "radio") {
                if (el.checked) data[el.name] = el.value;
            }
            else if (el.type === "checkbox") {
                if (!data[el.name]) data[el.name] = [];
                if (el.checked) data[el.name].push(el.value);
            }
            else {
                data[el.name] = el.value;
            }

        });

        localStorage.setItem("formData", JSON.stringify(data));
        localStorage.setItem("formStep", currentStep);
    }

     // ======================
    // CARGAR DATOS
    // ======================
    function loadData() {

        let saved = localStorage.getItem("formData");
        if (!saved) return;

        let data = JSON.parse(saved);

        Object.keys(data).forEach(name => {

            let elements = form.querySelectorAll(`[name="${name}"]`);

            elements.forEach(el => {

                if (el.type === "radio") {
                    if (el.value === data[name]) el.checked = true;
                }
                else if (el.type === "checkbox") {
                    if (data[name].includes(el.value)) el.checked = true;
                }
                else {
                    el.value = data[name];
                }

            });

        });

        let savedStep = localStorage.getItem("formStep");
        if (savedStep !== null) {
            currentStep = parseInt(savedStep);
        }
    }
    window.nextStep = function () {

        let current = steps[currentStep];
        let inputs = current.querySelectorAll("input, select, textarea");

        for (let input of inputs) {

            if (!input.name) continue;

            if (input.type !== "radio" && input.type !== "checkbox") {
                if (input.required && !input.value) {
                    alert("Completa los campos requeridos");
                    return;
                }
            }
        }

        let radios = [...current.querySelectorAll("input[type='radio']")];
        let names = [...new Set(radios.map(r => r.name))];

        for (let name of names) {
            let group = current.querySelectorAll(`input[name="${name}"]`);
            let checked = current.querySelector(`input[name="${name}"]:checked`);

            if (group[0].required && !checked) {
                alert("Selecciona una opción");
                return;
            }
        }

        if (currentStep < steps.length - 1) {
            currentStep++;
            showStep(currentStep);
        }
    };

    window.prevStep = function () {
        if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
        }
    };

    // 🔥 SELECT DINÁMICO
    document.querySelectorAll(".dynamic").forEach(select => {

        let tipo = select.dataset.type;

        fetch(`/data/${tipo}`)
        .then(res => res.json())
        .then(data => {

            select.innerHTML = '<option value="">Selecciona</option>';

            data.forEach(item => {
                let option = document.createElement("option");
                option.value = item;
                option.textContent = item;
                select.appendChild(option);
            });

        });

    });
// ======================
    // AUTOGUARDADO EN TIEMPO REAL
    // ======================
    form.addEventListener("input", saveData);

    // ======================
    // LIMPIAR AL ENVIAR
    // ======================
    window.clearStorage = function () {
        localStorage.removeItem("formData");
        localStorage.removeItem("formStep");
    };
    loadData();
    showStep(currentStep);

});
document.addEventListener("change", function (e) {

    if (e.target.tagName !== "SELECT") return;

    let select = e.target;

    // detectar si eligieron "Otro"
    if (select.value === "Otro") {

        // evitar duplicados
        if (select.parentNode.querySelector(".input-otro")) return;

        let input = document.createElement("input");
        input.type = "text";
        input.placeholder = "Especifica...";
        input.classList.add("input-otro");
        input.style.marginTop = "10px";

        // 🔥 usar el mismo name
        input.name = select.name;

        // 🔥 quitar name al select
        select.removeAttribute("name");

        select.parentNode.appendChild(input);

    } else {

        // si cambian de "Otro" a normal → quitar input
        let input = select.parentNode.querySelector(".input-otro");

        if (input) {
            input.remove();

            // devolver name al select
            select.name = input.name;
        }
    }

});

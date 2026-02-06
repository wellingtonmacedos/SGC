document.addEventListener("DOMContentLoaded", function () {
  const passwordInput = document.getElementById("password");
  const toggleBtn = document.getElementById("togglePassword");
  const owl = document.querySelector(".owl");

  if (!passwordInput || !owl) return;

  passwordInput.addEventListener("focus", function () {
    owl.classList.add("cover-eyes");
  });

  passwordInput.addEventListener("blur", function () {
    if (passwordInput.type === "password") {
      owl.classList.remove("cover-eyes");
    }
  });

  if (toggleBtn) {
    toggleBtn.addEventListener("click", function () {
      const isHidden = passwordInput.type === "password";

      passwordInput.type = isHidden ? "text" : "password";
      toggleBtn.textContent = isHidden ? "🙈" : "👁️";

      if (isHidden) {
        // Se estava escondido e agora mostra (text), descobre os olhos
        owl.classList.remove("cover-eyes");
      } else if (document.activeElement === passwordInput) {
        // Se estava mostrando e agora esconde (password), e está focado, cobre os olhos
        owl.classList.add("cover-eyes");
      }
    });
  }
});

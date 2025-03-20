document.addEventListener("DOMContentLoaded", function () {
  // Form validation
  const forms = document.querySelectorAll("form");

  forms.forEach((form) => {
    form.addEventListener("submit", function (event) {
      const requiredFields = form.querySelectorAll("[required]");
      let valid = true;

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          valid = false;
          field.classList.add("error-field");

          // Create error message if it doesn't exist
          let errorMsg = field.nextElementSibling;
          if (!errorMsg || !errorMsg.classList.contains("field-error")) {
            errorMsg = document.createElement("div");
            errorMsg.classList.add("field-error");
            errorMsg.textContent = "This field is required";
            field.parentNode.insertBefore(errorMsg, field.nextSibling);
          }
        } else {
          field.classList.remove("error-field");

          // Remove error message if it exists
          const errorMsg = field.nextElementSibling;
          if (errorMsg && errorMsg.classList.contains("field-error")) {
            errorMsg.remove();
          }
        }
      });

      if (!valid) {
        event.preventDefault();
      }
    });
  });

  // Add class animation on scroll
  window.addEventListener("scroll", function () {
    const features = document.querySelectorAll(".feature");

    features.forEach((feature) => {
      const featurePosition = feature.getBoundingClientRect().top;
      const screenPosition = window.innerHeight / 1.3;

      if (featurePosition < screenPosition) {
        feature.classList.add("show");
      }
    });
  });
});

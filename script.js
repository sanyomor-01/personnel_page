function updateSocialLinks() {
  const socialLinksDiv = document.getElementById("social_links");
  socialLinksDiv.innerHTML = ""; // Clear any previous input fields

  const platforms = document.querySelectorAll(
    "input[name='platforms[]']:checked"
  );
  ("input[name='platforms[]']:checked");

  platforms.forEach((platform) => {
    const platformName = platform.value;

    const label = document.createElement("label");
    label.textContent = " ${platformName}:";

    const input = document.createElement("input");
    input.type = "url";
    input.name = "social_links[${platformName}]";
    input.placeholder = "https://your profile link";
    input.required = true;

    socialLinksDiv.appendChild(label);
    socialLinksDiv.appendChild(input);
    socialLinksDiv.appendChild(document.createElement("br"));
  });
}


// Form validation function for editProfile
function validateForm() {
  const firstName = document.forms["editProfile"]["first_name"].value;
  const lastName = document.forms["editProfile"]["last_name"].value;
  const phoneNumber = document.forms["editProfile"]["phone"].value;
  const email = document.forms["editProfile"]["email"].value;
  const startDate = document.forms["editProfile"]["start_date"].value;
  const endDate = document.forms["editProfile"]["end_date"].value;

  // Check if any required fields are empty
  if (!firstName || !lastName || !phoneNumber || !email) {
      alert("Please fill out all required fields.");
      return false;
  }

  // Phone number format check (e.g., numbers only)
  const phonePattern = /^\d+$/;
  if (!phonePattern.test(phoneNumber)) {
      alert("Please enter a valid phone number.");
      return false;
  }

  // Email format check
  const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
  if (!emailPattern.test(email)) {
      alert("Please enter a valid email address.");
      return false;
  }

  // Check if end date is after start date
  if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
      alert("End Date cannot be before Start Date.");
      return false;
  }

  // If all validations pass
  return true;
}

// Attach the validateForm function to the form's onsubmit event
document.addEventListener("DOMContentLoaded", function() {
  const form = document.forms["editProfile"];
  if (form) {
      form.onsubmit = validateForm;
  }
});
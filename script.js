
function updateSocialLinks() {
    const selectedPlatforms = document.querySelectorAll('input[name="platforms[]"]:checked');
    const socialLinksDiv = document.getElementById('social_links');
    const existingFields = new Set(
        Array.from(socialLinksDiv.querySelectorAll('label')).map(label => label.dataset.platform)
    );

    // Loop through selected checkboxes
    selectedPlatforms.forEach(checkbox => {
        const platform = checkbox.value;

        if (!existingFields.has(platform)) {
            const label = document.createElement('label');
            label.innerText = platform;
            label.dataset.platform = platform;

            const input = document.createElement('input');
            input.type = 'text';
            input.name = `social_links[${platform}]`;
            input.placeholder = `Enter your ${platform} profile link`;

            // Append label and input field
            socialLinksDiv.appendChild(label);
            socialLinksDiv.appendChild(input);
            socialLinksDiv.appendChild(document.createElement('br')); 
        }
    });

    const uncheckedPlatforms = document.querySelectorAll('input[name="platforms[]"]:not(:checked)');
    uncheckedPlatforms.forEach(checkbox => {
        const platform = checkbox.value;
        const labelsToRemove = socialLinksDiv.querySelectorAll(`label[data-platform="${platform}"]`);

        labelsToRemove.forEach(label => {
            const inputToRemove = label.nextElementSibling;
            const brToRemove = inputToRemove.nextElementSibling;

            label.remove();
            inputToRemove.remove();
            brToRemove.remove();
        });
    });
}

  

function validateForm() {
  const firstName = document.forms["editProfile"]["first_name"].value;
  const lastName = document.forms["editProfile"]["last_name"].value;
  const phoneNumber = document.forms["editProfile"]["phone"].value;
  const email = document.forms["editProfile"]["email"].value;
  const startDate = document.forms["editProfile"]["start_date"].value;
  const endDate = document.forms["editProfile"]["end_date"].value;

  if (!firstName || !lastName || !phoneNumber || !email) {
      alert("Please fill out all required fields.");
      return false;
  }

  const phonePattern = /^\d+$/;
  if (!phonePattern.test(phoneNumber)) {
      alert("Please enter a valid phone number.");
      return false;
  }

  const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
  if (!emailPattern.test(email)) {
      alert("Please enter a valid email address.");
      return false;
  }

  if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
      alert("End Date cannot be before Start Date.");
      return false;
  }

  return true;
}

document.addEventListener("DOMContentLoaded", function() {
  const form = document.forms["editProfile"];
  if (form) {
      form.onsubmit = validateForm;
  }
});



    function validateForm() {
        const firstName = document.forms["profileForm"]["first_name"].value;
        const lastName = document.forms["profileForm"]["last_name"].value;
        const email = document.forms["profileForm"]["email"].value;
        const phone = document.forms["profileForm"]["phone"].value;
        const startDate = document.forms["profileForm"]["start_date"].value;
        const endDate = document.forms["profileForm"]["end_date"].value;

        if (firstName === "" || !/^[a-zA-Z]+$/.test(firstName)) {
            alert("Please enter a valid first name.");
            return false;
        }

        if (lastName === "" || !/^[a-zA-Z]+$/.test(lastName)) {
            alert("Please enter a valid last name.");
            return false;
        }

        const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailPattern.test(email)) {
            alert("Please enter a valid email address.");
            return false;
        }

        const phonePattern = /^\d{10,15}$/;
        if (!phonePattern.test(phone)) {
            alert("Please enter a valid phone number with 10-15 digits.");
            return false;
        }

        if (startDate && endDate && new Date(startDate) >= new Date(endDate)) {
            alert("End date must be after start date.");
            return false;
        }

        return true; 
    }

    function validatePasswordForm() {
      const newPassword = document.forms["changePasswordForm"]["new_password"].value;
      const confirmPassword = document.forms["changePasswordForm"]["confirm_password"].value;
  
      if (newPassword.length < 8 || !/[A-Z]/.test(newPassword) || !/[a-z]/.test(newPassword) || !/[0-9]/.test(newPassword)) {
          alert("New password must be at least 8 characters long and include an uppercase letter, lowercase letter, and a number.");
          return false;
      }
  
      if (newPassword !== confirmPassword) {
          alert("New password and confirmation do not match.");
          return false;
      }
  
      return true;
  }


  
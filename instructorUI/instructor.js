// Function to switch between tabs
function openTab(evt, tabName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
}

// Function to select a subject and fetch its competencies
function selectSubject(subjectCode, subjectName, buttonElement) {
  // Highlight the selected subject button
  document.querySelectorAll(".btnSubjects button").forEach(function (button) {
    button.classList.remove("selected-subject");
  });
  buttonElement.classList.add("selected-subject");

  // Store selected subject in sessionStorage
  sessionStorage.setItem("selectedSubjectCode", subjectCode);
  sessionStorage.setItem("selectedSubjectName", subjectName);

  // Update hidden input fields in the Competencies form
  document.getElementById("selected_subject_code").value = subjectCode;
  document.getElementById("selected_subject_name").value = subjectName;

  // Update hidden input fields in the Syllabus form
  document.getElementById("syllabus_subject_code").value = subjectCode;
  document.getElementById("syllabus_subject_name").value = subjectName;

  // Filter content by selected subject
  filterContentBySubject(subjectCode);

  // Fetch and display competencies for the selected subject
  fetchCompetencies(subjectCode);

  // Fetch and display comments for the selected subject
  fetchTopicsAndComments(subjectCode);
}

// Function to filter content by selected subject
function filterContentBySubject(subjectCode) {
  // Update Plan Cards
  document.querySelectorAll(".planCard").forEach(function (card) {
    if (
      card.getAttribute("data-subject-code") === subjectCode ||
      card.getAttribute("data-subject-code") === ""
    ) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });

  // Update Competency Items
  document.querySelectorAll(".remarksTable").forEach(function (table) {
    if (
      table.getAttribute("data-subject-code") === subjectCode ||
      table.getAttribute("data-subject-code") === ""
    ) {
      table.style.display = "table";
    } else {
      table.style.display = "none";
    }
  });

  // Update Comments
  document.querySelectorAll(".commentCard").forEach(function (card) {
    if (
      card.closest("#containerComment").getAttribute("data-subject-code") ===
        subjectCode ||
      card.closest("#containerComment").getAttribute("data-subject-code") === ""
    ) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
}

function fetchCompetencies(subjectCode) {
  const request = new XMLHttpRequest();
  request.open("POST", "fetch_competencies.php", true);
  request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  request.onload = function () {
    if (this.status === 200) {
      const response = JSON.parse(this.responseText);
      const tableBody = document.querySelector("#competenciesTable");
      const noCompetenciesRow = document.getElementById("noCompetencies");

      // Clear existing rows
      tableBody
        .querySelectorAll("tr:not(:first-child)")
        .forEach((row) => row.remove());

      if (response.length > 0) {
        response.forEach(function (competency) {
          const row = document.createElement("tr");

          // Adding padding and spacing directly into the HTML structure
          row.innerHTML = `
            <td style="padding: 10px 15px; text-align: left;">${competency.competency_description}</td>
            <td style="padding: 10px 15px; text-align: center;">
                <label>
                    <input type="checkbox" disabled ${
                      competency.remarks === "IMPLEMENTED" ? "checked" : ""
                    } style="transform: scale(2.3); margin-right: 10px; overflow-y: auto;" />
                </label>
            </td>
          `;
          tableBody.appendChild(row);
        });
      } else {
        const noCompetenciesRow = document.createElement("tr");
        noCompetenciesRow.innerHTML = `<td colspan="2">No competencies found for this subject.</td>`;
        tableBody.appendChild(noCompetenciesRow);
      }
    }
  };
  request.send(`subject_code=${encodeURIComponent(subjectCode)}`);
}

// Initialize the page and auto-select the previously selected subject
document.addEventListener("DOMContentLoaded", function () {
  // Check if a subject was selected before
  var selectedSubjectCode = sessionStorage.getItem("selectedSubjectCode");
  var selectedSubjectName = sessionStorage.getItem("selectedSubjectName");

  if (selectedSubjectCode && selectedSubjectName) {
    // Auto-select the subject if previously selected
    var subjectButton = document.querySelector(
      `.btnSubjects button[onclick*="${selectedSubjectCode}"]`
    );
    if (subjectButton) {
      selectSubject(selectedSubjectCode, selectedSubjectName, subjectButton);
    }
  }
});

document.addEventListener("DOMContentLoaded", function () {
  // Event listener to handle the subject button click
  document.querySelectorAll(".btnSubjects button").forEach((button) => {
    button.addEventListener("click", function () {
      const subjectCode = this.getAttribute("data-subject-code");
      filterCompetencies(subjectCode);
    });
  });
});

function filterCompetencies(subjectCode) {
  const request = new XMLHttpRequest();
  request.open(
    "GET",
    `fetch_competencies.php?action=fetch_competencies&subject_code=${subjectCode}`,
    true
  );
  request.onload = function () {
    if (this.status === 200) {
      const response = JSON.parse(this.responseText);
      const competencyContainer = document.getElementById(
        "competencyContainer"
      );
      competencyContainer.innerHTML = "";

      if (response.length > 0) {
        response.forEach(function (competency) {
          const competencyElement = document.createElement("div");
          competencyElement.className = "planCard";
          competencyElement.innerHTML = `<p>${competency.competency_description}</p>`;
          competencyContainer.appendChild(competencyElement);
        });
      } else {
        competencyContainer.innerHTML =
          "<p>No competencies found for this subject.</p>";
      }
    }
  };
  request.send();
}

function showLogoutMessage(message) {
  var logoutMessage = document.getElementById("logoutMessage");
  logoutMessage.textContent = message;
  logoutMessage.style.display = "block";
  setTimeout(function () {
    logoutMessage.style.display = "none";
  }, 3000);
}

// Function to fetch Topics and Comments for the selected subject via AJAX
function fetchTopicsAndComments(subjectCode) {
  const formData = new FormData();
  formData.append("subject_code", subjectCode);

  console.log("Sending subject code:", subjectCode); // Check if the subject code is correct

  fetch("fetch_comments.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("Data received:", data); // For debugging purposes
      appendCommentsToContainer(data);
    })
    .catch((error) => {
      console.error("Error fetching topics and comments:", error);
      document.querySelector("#containerComment").innerHTML =
        "<p>Error fetching comments. Please try again later.</p>";
    });
}

// Function to dynamically append the comments to the comments container
function appendCommentsToContainer(data) {
  const commentContainer = document.querySelector("#containerComment");

  if (!commentContainer) {
    console.error("Comment container not found.");
    return;
  }

  // Clear previous content
  commentContainer.innerHTML = "";

  let hasData = false;

  // Check if the data has comments or show "no comments"
  if (data && Object.keys(data).length > 0) {
    hasData = true;

    let commentCounter = 1; // Initialize a counter for comments

    // Iterate over ILOs
    Object.keys(data).forEach((iloKey) => {
      const iloData = data[iloKey];

      if (iloData.comments && iloData.comments.length > 0) {
        iloData.comments.forEach((comment) => {
          const commentHTML = `
                    <div class="commentCard">
                        <p><strong>Comment #${commentCounter}</strong><br> <strong>ILO:</strong> ${
            iloData.ilo || "N/A"
          }</p>
                        <p><strong>Comment: </strong>${
                          comment.comment || "No comment provided"
                        }</p>
            <p><strong>Date: </strong> ${new Date(comment.timestamp)
              .toLocaleString("en-US", {
                hour: "numeric",
                minute: "numeric",
                hour12: true,
                year: "numeric",
                month: "long",
                day: "numeric",
              })
              .replace(" at ", " || ")}</p>
                    </div>
                  `;
          // Append each comment for the ILO
          commentContainer.innerHTML += commentHTML;
          commentCounter++; // Increment the counter for each comment
        });
      } else {
        commentContainer.innerHTML += `<p>No comments for ILO: ${
          iloData.ilo || "N/A"
        }</p>`;
      }
    });
  }

  if (!hasData) {
    commentContainer.innerHTML =
      "<p>No comments available for the selected subject.</p>";
  }
}

// Automatically fetch comments for the default or selected subject
document.querySelectorAll(".btnSubjects button").forEach((button) => {
  button.addEventListener("click", function () {
    const subjectCode = this.getAttribute("data-subject-code");
    console.log("Button clicked for subject:", subjectCode); // For debugging the clicked subject
    fetchTopicsAndComments(subjectCode); // Fetch the topics and comments
  });
});

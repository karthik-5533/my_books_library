// Function to toggle dropdown visibility
function toggleDropdown(id) {
    const dropdown = document.getElementById(id); // Get the dropdown content
    const header = dropdown.previousElementSibling; // Get the header element
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block"; // Toggle visibility
    header.classList.toggle('active'); // Rotate arrow
}

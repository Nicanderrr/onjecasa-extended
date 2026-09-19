 
 function doit(){
var body = document.getElementsByTagName("body")[0];
if(body.classList.contains("dark-mode")){
   body.classList.remove("dark-mode") ;
}else{
    body.classList.add("dark-mode") ;

}


}


// // Get the current state of dark mode
// let darkMode = localStorage.getItem("darkMode");

// // if dark mode is not set, set it to off
// if (darkMode === null) {
//     darkMode = "off";
//     localStorage.setItem("darkMode", darkMode);
// }

// // get the dark mode toggle button
// const toggleBtn = document.getElementById("toggleBtn");

// // add event listener to toggle button
// toggleBtn.addEventListener("click", function() {
//     // toggle dark mode
//     if (darkMode === "off") {
//         darkMode = "on";
//     } else {
//         darkMode = "off";
//     }

//     // save the new state of dark mode to local storage
//     localStorage.setItem("darkMode", darkMode);

//     // Apply the changes to the body element
//     document.body.classList.toggle("dark-mode");
// });
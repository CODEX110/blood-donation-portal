// Client-side form validation
function validateRegistration() {
    let name = document.forms['regForm']['name'].value.trim();
    let email = document.forms['regForm']['email'].value.trim();
    let phone = document.forms['regForm']['phone'].value.trim();
    if (name === '' || email === '' || phone === '') {
        alert('Name, email and phone are required.');
        return false;
    }
    // basic email check
    let re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\\.,;:\s@\"]+\.)+[^<>()[\]\\.,;:\s@\"]{2,})$/i;
    if (!re.test(email)) {
        alert('Please enter a valid email address.');
        return false;
    }
    return true;
}

// simple fade-in on scroll
function revealOnScroll() {
    const elements = document.querySelectorAll('.container h2, .container p, .slider');
    const windowHeight = window.innerHeight;
    elements.forEach(el => {
        const position = el.getBoundingClientRect().top;
        if (position < windowHeight - 50) {
            el.classList.add('visible');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    revealOnScroll();
    // greet user if chatbot exists
    const cb = document.getElementById('chatbox');
    if (cb && typeof addMessage === 'function') {
        addMessage('Hello! I am your blood donation assistant. Ask me questions about blood donation or registration.', 'bot');
    }
    // admin helpers
    enablePasswordToggle();
    makeTableSearchable('#donor-table', '#donor-search');
    makeTableSortable('#donor-table');
    makeTableSearchable('#request-table', '#request-search');
    makeTableSortable('#request-table');
    makeTableSearchable('#camp-table', '#camp-search');
    makeTableSortable('#camp-table');
    // slider
    initSlider();
    // initialize extras
    initTestimonials();
    initDarkMode();
    // typewriter cursor
    const tw = document.querySelector('.typewriter');
    if (tw) tw.style.borderRight = '2px solid #fff';
});
window.addEventListener('scroll', revealOnScroll);

// initialize slider controls
function initSlider() {
    const slides = document.querySelectorAll('#slider .slide');
    const dotsContainer = document.getElementById('dots');
    if (!slides.length || !dotsContainer) return;
    let index = 0;
    function showSlide(i) {
        const slidesDiv = document.querySelector('#slider .slides');
        slidesDiv.style.transform = `translateX(-${i*100}%)`;
        updateDots(i);
    }
    function updateDots(i) {
        dotsContainer.innerHTML = '';
        slides.forEach((_, idx) => {
            const span = document.createElement('span');
            span.className = idx === i ? 'active' : '';
            span.addEventListener('click', () => { index = idx; showSlide(index); });
            dotsContainer.appendChild(span);
        });
    }
    document.getElementById('prev').addEventListener('click', () => {
        index = (index - 1 + slides.length) % slides.length;
        showSlide(index);
    });
    document.getElementById('next').addEventListener('click', () => {
        index = (index + 1) % slides.length;
        showSlide(index);
    });
    showSlide(index);
    setInterval(() => { index = (index + 1) % slides.length; showSlide(index); }, 5000);
}


// testimonial slider
function initTestimonials() {
    const items = document.querySelectorAll('#testimonial-slider .testi-item');
    let t=0;
    function show() {
        items.forEach((el,i)=> el.style.display = i===t?'block':'none');
        t = (t+1)%items.length;
    }
    if (items.length) {
        show();
        setInterval(show,5000);
    }
}

// dark mode toggle and storage
function initDarkMode() {
    const chk = document.getElementById('darkmode');
    if (!chk) return;
    const apply = (on)=>{
        if(on) document.body.classList.add('dark-mode');
        else document.body.classList.remove('dark-mode');
    };
    const stored = localStorage.getItem('darkmode');
    if (stored==='true') { chk.checked=true; apply(true); }
    chk.addEventListener('change',()=>{
        apply(chk.checked);
        localStorage.setItem('darkmode',chk.checked);
    });
}

// toast notification
function showToast(msg) {
    let t = document.createElement('div');
    t.className='toast';
    t.textContent=msg;
    document.body.appendChild(t);
    t.style.display='block';
    setTimeout(()=>{t.style.opacity='0';},3000);
    setTimeout(()=>{document.body.removeChild(t);},4000);
}

// admin utilities
function makeTableSearchable(tableSelector, inputSelector) {
    const table = document.querySelector(tableSelector);
    const input = document.querySelector(inputSelector);
    if (!table || !input) return;
    input.addEventListener('input', () => {
        const filter = input.value.toLowerCase();
        Array.from(table.tBodies[0].rows).forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
}

function makeTableSortable(tableSelector) {
    const table = document.querySelector(tableSelector);
    if (!table) return;
    const headers = table.querySelectorAll('th');
    headers.forEach((th, index) => {
        th.addEventListener('click', () => {
            const tbody = table.tBodies[0];
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const asc = !th.asc;
            th.asc = asc;
            rows.sort((a, b) => {
                const aText = a.cells[index].textContent.trim();
                const bText = b.cells[index].textContent.trim();
                return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });
            rows.forEach(r => tbody.appendChild(r));
        });
    });
}

// password toggle
function enablePasswordToggle() {
    const checkbox = document.getElementById('showpwd');
    const pwd = document.getElementById('pwd');
    if (checkbox && pwd) {
        checkbox.addEventListener('change', () => {
            pwd.type = checkbox.checked ? 'text' : 'password';
        });
    }
}

// chatbot functionality
const chatbox = document.getElementById('chatbox');
if (chatbox) {
    const chatheader = document.getElementById('chatheader');
    const chatcontent = document.getElementById('chatcontent');
    const chatinput = document.getElementById('chatinput');

    // toggle visibility
    chatheader.addEventListener('click', () => {
        if (chatcontent.style.display === 'none') {
            chatcontent.style.display = 'block';
            chatinput.style.display = 'block';
        } else {
            chatcontent.style.display = chatinput.style.display = 'none';
        }
    });

    // simple response map
    const responses = {
    'blood donation': 'Blood donation saves lives and is safe for healthy people aged 18-65. Visit "Register as Donor" to sign up.',
    'how to register': 'Go to Register as Donor page and fill the form with your details.',
    'where is college': 'Universal College of Arts and Science is located in Mannarkkad.',
    'eligibility': 'You should be 18-65 years old, healthy, and weigh at least 50kg to donate.',
    'contact admin': 'Admins can be reached via the portal admin login or college office.',
    'emergency': 'Use the Emergency tab to submit a request for urgent blood needs.'
};

function addMessage(text, sender) {
    const div = document.createElement('div');
    div.className = sender;
    div.textContent = text;
    chatcontent.appendChild(div);
    chatcontent.scrollTop = chatcontent.scrollHeight;
}

chatinput.addEventListener('keydown', e => {
    if (e.key === 'Enter' && chatinput.value.trim() !== '') {
        const question = chatinput.value.trim();
        addMessage(question, 'user');
        let answer = 'Sorry, I did not understand. Try asking about blood donation or registration.';
        const key = question.toLowerCase();
        for (let phrase in responses) {
            if (key.includes(phrase)) {
                answer = responses[phrase];
                break;
            }
        }
        setTimeout(() => addMessage(answer, 'bot'), 500);
            chatinput.value = '';
        }
    });
}
// Dark Mode Toggle
const toggle = document.getElementById("darkmode");

toggle.addEventListener("change", function () {
    document.body.classList.toggle("dark");

    // Save preference
    if(document.body.classList.contains("dark")){
        localStorage.setItem("theme", "dark");
    } else {
        localStorage.setItem("theme", "light");
    }
});

// Load saved theme
window.addEventListener("load", function(){
    const savedTheme = localStorage.getItem("theme");
    if(savedTheme === "dark"){
        document.body.classList.add("dark");
        toggle.checked = true;
    }
});
const themeSwitch = document.getElementById("themeSwitch");

// Toggle Dark Mode
themeSwitch.addEventListener("click", () => {
    document.body.classList.toggle("dark");

    if(document.body.classList.contains("dark")){
        localStorage.setItem("theme", "dark");
    } else {
        localStorage.setItem("theme", "light");
    }
});

// Load Saved Theme
window.addEventListener("load", () => {
    const savedTheme = localStorage.getItem("theme");

    if(savedTheme === "dark"){
        document.body.classList.add("dark");
    }
});

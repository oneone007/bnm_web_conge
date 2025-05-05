

// document.addEventListener('DOMContentLoaded', () => {
//     const toggle = document.getElementById('products-toggle');
//     const submenu = document.getElementById('products-submenu');
//     toggle.addEventListener('click', () => {
//         submenu.classList.toggle('hidden');
//     });

//     const recapsToggle = document.getElementById('recaps-toggle');
//     const recapsSubmenu = document.getElementById('recaps-submenu');
//     recapsToggle.addEventListener('click', () => {
//         recapsSubmenu.classList.toggle('hidden');
//     });
// });

function toggleSubmenu(id) {
  const submenu = document.getElementById(id);
  submenu.classList.toggle('show');
}

document.querySelectorAll('.stars').forEach(star => {
    star.addEventListener('click', function() {
        // Log the rating value when a star is clicked
        let rating = this.getAttribute('data-value');
        console.log('Rating selected: ', rating); // Debugging log

        // Remove previous selections
        document.querySelectorAll('.stars').forEach(s => s.classList.remove('selected'));
        
        // Highlight selected stars
        for (let i = 1; i <= rating; i++) {
            document.querySelector('.stars[data-value="' + i + '"]').classList.add('selected');
        }

        // Log to ensure the stars are being highlighted
        console.log('Stars highlighted up to: ', rating);

        // Send rating to backend (same page)
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'rating=' + rating
        })
        .then(response => response.text()) // Expect a plain text response
        .then(data => {
            // Display the response in the result paragraph
            console.log('Server response:', data); // Log the server response
            document.getElementById('result').innerText = data;
        })
        .catch(error => {
            console.error('Error:', error); // Log any errors
        });
    });
});


document.querySelectorAll('.logoutButton').forEach(button => {
    button.state = 'default'
  
    // function to transition a button from one state to the next
    let updateButtonState = (button, state) => {
      if (logoutButtonStates[state]) {
        button.state = state
        for (let key in logoutButtonStates[state]) {
          button.style.setProperty(key, logoutButtonStates[state][key])
        }
      }
    }
  
    // mouse hover listeners on button
    button.addEventListener('mouseenter', () => {
      if (button.state === 'default') {
        updateButtonState(button, 'hover')
      }
    })
    button.addEventListener('mouseleave', () => {
      if (button.state === 'hover') {
        updateButtonState(button, 'default')
      }
    })
  
    // click listener on button
    button.addEventListener('click', () => {
      if (button.state === 'default' || button.state === 'hover') {
        button.classList.add('clicked')
        updateButtonState(button, 'walking1')
        setTimeout(() => {
          button.classList.add('door-slammed')
          updateButtonState(button, 'walking2')
          setTimeout(() => {
            button.classList.add('falling')
            updateButtonState(button, 'falling1')
            setTimeout(() => {
              updateButtonState(button, 'falling2')
              setTimeout(() => {
                updateButtonState(button, 'falling3')
                setTimeout(() => {
                  button.classList.remove('clicked')
                  button.classList.remove('door-slammed')
                  button.classList.remove('falling')
                  updateButtonState(button, 'default')
                       // ✅ Redirect here after full animation
                window.location.href = 'db/logout.php';
                }, 1000)
              }, logoutButtonStates['falling2']['--walking-duration'])
            }, logoutButtonStates['falling1']['--walking-duration'])
          }, logoutButtonStates['walking2']['--figure-duration'])
        }, logoutButtonStates['walking1']['--figure-duration'])
        
      }
    })
  })
  
  const logoutButtonStates = {
    'default': {
      '--figure-duration': '100',
      '--transform-figure': 'none',
      '--walking-duration': '100',
      '--transform-arm1': 'none',
      '--transform-wrist1': 'none',
      '--transform-arm2': 'none',
      '--transform-wrist2': 'none',
      '--transform-leg1': 'none',
      '--transform-calf1': 'none',
      '--transform-leg2': 'none',
      '--transform-calf2': 'none'
    },
    'hover': {
      '--figure-duration': '100',
      '--transform-figure': 'translateX(1.5px)',
      '--walking-duration': '100',
      '--transform-arm1': 'rotate(-5deg)',
      '--transform-wrist1': 'rotate(-15deg)',
      '--transform-arm2': 'rotate(5deg)',
      '--transform-wrist2': 'rotate(6deg)',
      '--transform-leg1': 'rotate(-10deg)',
      '--transform-calf1': 'rotate(5deg)',
      '--transform-leg2': 'rotate(20deg)',
      '--transform-calf2': 'rotate(-20deg)'
    },
    'walking1': {
      '--figure-duration': '300',
      '--transform-figure': 'translateX(11px)',
      '--walking-duration': '300',
      '--transform-arm1': 'translateX(-4px) translateY(-2px) rotate(120deg)',
      '--transform-wrist1': 'rotate(-5deg)',
      '--transform-arm2': 'translateX(4px) rotate(-110deg)',
      '--transform-wrist2': 'rotate(-5deg)',
      '--transform-leg1': 'translateX(-3px) rotate(80deg)',
      '--transform-calf1': 'rotate(-30deg)',
      '--transform-leg2': 'translateX(4px) rotate(-60deg)',
      '--transform-calf2': 'rotate(20deg)'
    },
    'walking2': {
      '--figure-duration': '400',
      '--transform-figure': 'translateX(17px)',
      '--walking-duration': '300',
      '--transform-arm1': 'rotate(60deg)',
      '--transform-wrist1': 'rotate(-15deg)',
      '--transform-arm2': 'rotate(-45deg)',
      '--transform-wrist2': 'rotate(6deg)',
      '--transform-leg1': 'rotate(-5deg)',
      '--transform-calf1': 'rotate(10deg)',
      '--transform-leg2': 'rotate(10deg)',
      '--transform-calf2': 'rotate(-20deg)'
    },
    'falling1': {
      '--figure-duration': '1600',
      '--walking-duration': '400',
      '--transform-arm1': 'rotate(-60deg)',
      '--transform-wrist1': 'none',
      '--transform-arm2': 'rotate(30deg)',
      '--transform-wrist2': 'rotate(120deg)',
      '--transform-leg1': 'rotate(-30deg)',
      '--transform-calf1': 'rotate(-20deg)',
      '--transform-leg2': 'rotate(20deg)'
    },
    'falling2': {
      '--walking-duration': '300',
      '--transform-arm1': 'rotate(-100deg)',
      '--transform-arm2': 'rotate(-60deg)',
      '--transform-wrist2': 'rotate(60deg)',
      '--transform-leg1': 'rotate(80deg)',
      '--transform-calf1': 'rotate(20deg)',
      '--transform-leg2': 'rotate(-60deg)'
    },
    'falling3': {
      '--walking-duration': '500',
      '--transform-arm1': 'rotate(-30deg)',
      '--transform-wrist1': 'rotate(40deg)',
      '--transform-arm2': 'rotate(50deg)',
      '--transform-wrist2': 'none',
      '--transform-leg1': 'rotate(-30deg)',
      '--transform-leg2': 'rotate(20deg)',
      '--transform-calf2': 'none'
    }
    
  }

  

    






function navigateToPage(page) {
    window.location.href = page;
}


const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
const content = document.querySelector('.content');
const modeRadios = document.querySelectorAll('input[name="mode"]');

// Load mode from localStorage or default to 'auto'
let currentMode = localStorage.getItem('sidebarMode') || 'auto';

// Set radio button based on saved mode
modeRadios.forEach(radio => {
    radio.checked = (radio.value === currentMode);
});

// Update mode when user changes selection
modeRadios.forEach(radio => {
    radio.addEventListener('change', () => {
        currentMode = radio.value;
        localStorage.setItem('sidebarMode', currentMode);  // Save to localStorage
        updateSidebarBehavior();
    });
});

// Apply behavior on page load
updateSidebarBehavior();

function updateSidebarBehavior() {
    if (currentMode === 'auto') {
        sidebarToggle.style.display = 'none';
        document.addEventListener('mousemove', handleMouseMove);
        sidebar.addEventListener('mouseleave', handleMouseLeave);
    } else {
        sidebarToggle.style.display = 'block';
        document.removeEventListener('mousemove', handleMouseMove);
        sidebar.removeEventListener('mouseleave', handleMouseLeave);
    }
}

// Auto mode handlers
function handleMouseMove(event) {
    if (event.clientX < 50) {
        sidebar.classList.remove('sidebar-hidden');
        content.classList.remove('content-full');
        sidebarToggle.style.left = '260px';
    }
}

function handleMouseLeave() {
    sidebar.classList.add('sidebar-hidden');
    content.classList.add('content-full');
    sidebarToggle.style.left = '10px';
}

// Manual toggle button
sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('sidebar-hidden');
    content.classList.toggle('content-full');

    sidebarToggle.style.left = sidebar.classList.contains('sidebar-hidden') ? '10px' : '260px';
});
// Initialize based on default (auto)
updateSidebarBehavior();



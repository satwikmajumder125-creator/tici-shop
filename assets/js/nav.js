const menu = document.querySelector(".menu");
const menuInner = menu.querySelector(".menu__inner");
const burger = document.querySelector(".burger");
const overlay = document.querySelector(".overlay");

// Navigation stack for submenu navigation
let navigationStack = [];
let menuLevel = 0;

// Initialize menu
function initMenu() {
  // Only hide submenus on mobile using CSS classes
  if (window.innerWidth < 768) {
    const allSubmenus = document.querySelectorAll('.submenu');
    allSubmenus.forEach(submenu => {
      submenu.classList.remove('is-current-slide', 'is-current-parent');
    });
    
    // Show main menu
    menuInner.classList.add('is-current-slide');
  }
}

// Navbar Menu Toggle Function
function toggleMenu() {
  menu.classList.toggle("is-active");
  overlay.classList.toggle("is-active");
  // DO NOT resetMenuState when opening the menu, keep the state of open submenus
  if (window.innerWidth < 768) {
    if (menu.classList.contains('is-active')) {
      document.body.classList.add('scroll-lock');
    } else {
      document.body.classList.remove('scroll-lock');
    }
  }
}

// Reset menu state
function resetMenuState() {
  navigationStack = [menuInner];
  menuLevel = 0;
  setMenuInnerTransform();
  const allSubmenus = document.querySelectorAll('.submenu');
  allSubmenus.forEach(submenu => {
    submenu.classList.remove('is-current-slide', 'is-current-parent');
    const backBtn = submenu.querySelector('.back-btn');
    if (backBtn) backBtn.remove();
  });
  menuInner.classList.remove('is-current-parent', 'is-current-slide');
}

function setMenuInnerTransform() {
  menuInner.style.transform = `translateX(-${menuLevel * 100}%)`;
}

// Handle submenu navigation
function handleSubmenuNavigation() {
  const menuItems = document.querySelectorAll('.menu__item');
  
  menuItems.forEach(item => {
    const link = item.querySelector('.menu__link');
    const submenu = item.querySelector('.submenu');
    
    if (link && submenu) {
      // Desktop hover behavior: DO NOT add/remove class is-current-slide, let CSS handle it
      // if (window.innerWidth >= 768) {
      //   item.addEventListener('mouseenter', () => {
      //     if (!item.classList.contains('back-btn')) {
      //       submenu.classList.add('is-current-slide');
      //     }
      //   });
      //   item.addEventListener('mouseleave', () => {
      //     submenu.classList.remove('is-current-slide');
      //   });
      // }
      // Mobile click behavior
      link.addEventListener('click', (e) => {
        e.preventDefault();
        // Handle back button
        if (item.classList.contains('back-btn')) {
          goBack();
          return;
        }
        // Navigate to submenu on mobile only
        if (window.innerWidth < 768) {
          navigateToSubmenu(submenu);
        }
      });
    }
  });
}

function clearAllSlideClasses() {
  document.querySelectorAll('.submenu, .menu__inner').forEach(sub => {
    sub.classList.remove('is-current-slide', 'is-current-parent');
  });
}

// Navigate to submenu
function navigateToSubmenu(submenu) {
  clearAllSlideClasses();
  for (let i = 0; i < navigationStack.length; i++) {
    navigationStack[i].classList.add('is-current-parent');
  }
  addBackButton(submenu);
  submenu.classList.add('is-current-slide');
  // Only push if submenu is not already the last element in the stack
  if (navigationStack[navigationStack.length - 1] !== submenu) {
    navigationStack.push(submenu);
    menuLevel++;
    setMenuInnerTransform();
  }
}

// Add back button to submenu
function addBackButton(submenu) {
  submenu.querySelectorAll('.back-btn').forEach(btn => btn.remove());

  const backBtn = document.createElement('li');
  backBtn.className = 'menu__item back-btn';

  const backLink = document.createElement('a');
  backLink.href = '#';
  backLink.className = 'menu__link';

  const backIcon = document.createElement('i');
  backIcon.className = 'bx bx-chevron-left';

  // Find parent li.menu__item of the current submenu
  let backTextLabel = 'Back';
  const parentLi = submenu.parentElement && submenu.parentElement.classList.contains('menu__item')
    ? submenu.parentElement
    : submenu.closest('.menu__item');
  if (parentLi) {
    // Get the .menu__link sibling of the submenu
    const parentLink = Array.from(parentLi.children).find(
      el => el.classList && el.classList.contains('menu__link')
    );
    if (parentLink) {
      backTextLabel = 'Back to ' + parentLink.textContent.trim();
    }
  } else if (submenu.closest('.menu__inner')) {
    backTextLabel = 'Back to Menu';
  }

  const backText = document.createTextNode(backTextLabel);

  backLink.appendChild(backIcon);
  backLink.appendChild(backText);
  backBtn.appendChild(backLink);

  submenu.insertBefore(backBtn, submenu.firstChild);

  backLink.addEventListener('click', (e) => {
    e.preventDefault();
    goBack();
  });
}

// Go back to previous menu
function goBack() {
  if (navigationStack.length > 1) {
    navigationStack.pop();
    const currentSubmenu = navigationStack[navigationStack.length - 1];
    clearAllSlideClasses();
    // Re-set is-current-parent for all remaining parent menus in the stack (except the current menu)
    for (let i = 0; i < navigationStack.length - 1; i++) {
      navigationStack[i].classList.add('is-current-parent');
    }
    currentSubmenu.classList.add('is-current-slide');
    menuLevel--;
    setMenuInnerTransform();
  }
}

// Initialize navigation stack with main menu
function initNavigationStack() {
  navigationStack = [menuInner];
}

function init() {
  initNavigationStack();
  handleSubmenuNavigation();
  
  // Handle resize
  window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) {
      // Remove all slide state classes on all submenus and menu__inner
      document.querySelectorAll('.submenu, .menu__inner').forEach(submenu => {
        submenu.classList.remove('is-current-slide', 'is-current-parent');
        // Remove back buttons on desktop
        const backBtn = submenu.querySelector('.back-btn');
        if (backBtn) backBtn.remove();
      });
      // Reset navigation stack and menuLevel
      navigationStack = [menuInner];
      menuLevel = 0;
      setMenuInnerTransform();
    }
  });
}

// Event listeners
burger.addEventListener("click", toggleMenu);
overlay.addEventListener("click", () => {
  menu.classList.remove("is-active");
  overlay.classList.remove("is-active");
  // DO NOT resetMenuState(); keep the state of open submenus
  if (window.innerWidth < 768) {
    document.body.classList.remove('scroll-lock');
  }
});

// Fixed Navbar Menu on Window Resize
window.addEventListener("resize", () => {
  if (window.innerWidth >= 768) {
    if (menu.classList.contains("is-active")) {
      toggleMenu();
    }
    document.body.classList.remove('scroll-lock');
  }
});

// Dark and Light Mode with localStorage
(function () {
  let darkMode = localStorage.getItem("darkMode");
  const darkSwitch = document.getElementById("switch");

  // Enable and Disable Darkmode
  const enableDarkMode = () => {
    document.body.classList.add("darkmode");
    localStorage.setItem("darkMode", "enabled");
  };

  const disableDarkMode = () => {
    document.body.classList.remove("darkmode");
    localStorage.setItem("darkMode", null);
  };

  // If the user previously enabled dark mode, enable it
  if (darkMode === "enabled") {
    enableDarkMode();
  }

  // When someone clicks the button
  darkSwitch.addEventListener("click", () => {
    darkMode = localStorage.getItem("darkMode");
    if (darkMode !== "enabled") {
      enableDarkMode();
    } else {
      disableDarkMode();
    }
  });
})();

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  initMenu();
  init();
});
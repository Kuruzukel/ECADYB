const themes = {
  "Light Mode": {
    "--primary-bg": "#ffffff",
    "--header-bg": "#94a3b8",
    "--accent": "#fcda15",
    "--section-bg": "#f8fafc",
    "--section-header": "#cbd5e1",
    "--body-bg": "#64748b",
    "--sidebar-bg": "#94a3b8",
    "--content-bg": "#ffffff",
    "--menu-bg-active": "#cbd5e1",
    "--menu-border-active": "#64748b",
    "--menu-hover-bg": "#e2e8f0",
  },
  "Dark Mode": {
    "--primary-bg": "#0f172a",
    "--header-bg": "#1e293b",
    "--accent": "#fcda15",
    "--section-bg": "#334155",
    "--section-header": "#475569",
    "--body-bg": "#0f172a",
    "--sidebar-bg": "#1e293b",
    "--content-bg": "#334155",
    "--menu-bg-active": "#475569",
    "--menu-border-active": "#334155",
    "--menu-hover-bg": "#64748b",
  },
  "Theme 1": {
    "--primary-bg": "#470a0a",
    "--header-bg": "#b21c0e",
    "--accent": "#fcda15",
    "--section-bg": "#bc4f5e",
    "--section-header": "#cb5382",
    "--body-bg": "#470a0a",
    "--sidebar-bg": "#b21c0e",
    "--content-bg": "#bc4f5e",
    "--menu-bg-active": "#cb5382",
    "--menu-border-active": "#fff176",
    "--menu-hover-bg": "#cb5382",
  },
  "Theme 2": {
    "--primary-bg": "#12086F",
    "--header-bg": "#2B35AF",
    "--accent": "#fcda15",
    "--section-bg": "#4895EF",
    "--section-header": "#4CC9F0",
    "--body-bg": "#12086F",
    "--sidebar-bg": "#2B35AF",
    "--content-bg": "#4895EF",
    "--menu-bg-active": "#4CC9F0",
    "--menu-border-active": "#ffffff",
    "--menu-hover-bg": "#4361EE",
  },
  "Theme 3": {
    "--primary-bg": "#0d381e",
    "--header-bg": "#164f2c",
    "--accent": "#fcda15",
    "--section-bg": "#2a834d",
    "--section-header": "#349e5e",
    "--body-bg": "#0d381e",
    "--sidebar-bg": "#164f2c",
    "--content-bg": "#2a834d",
    "--menu-bg-active": "#349e5e",
    "--menu-border-active": "#ffffff",
    "--menu-hover-bg": "#1f693c",
  },
  "Theme 4": {
    "--primary-bg": "#281E18",
    "--header-bg": "#572D0C",
    "--accent": "#fcda15",
    "--section-bg": "#E3B76A",
    "--section-header": "#9D9C75",
    "--body-bg": "#281E18",
    "--sidebar-bg": "#572D0C",
    "--content-bg": "#E3B76A",
    "--menu-bg-active": "#9D9C75",
    "--menu-border-active": "#ffffff",
    "--menu-hover-bg": "#C78E3A",
  },
  Default: {
    "--primary-bg": "#112d4e",
    "--header-bg": "#0c27be",
    "--accent": "#fcda15",
    "--section-bg": "#34495e",
    "--section-header": "#217ff7",
    "--body-bg": "#000042",
    "--sidebar-bg": "#0c27be",
    "--content-bg": "#112d4e",
    "--menu-bg-active": "#000042",
    "--menu-border-active": "#fcda15",
    "--menu-hover-bg": "#1c1c84",
  },
};

let pendingTheme = null;

function selectColor(el) {
  document
    .querySelectorAll(".color-box")
    .forEach((box) => box.classList.remove("selected"));
  el.classList.add("selected");

  pendingTheme = el.getAttribute("data-label");
  document.getElementById("modal-overlay").style.display = "flex";
}

function applyTheme(theme) {
  console.log("Applying theme:", theme);
  const root = document.documentElement;
  const selectedTheme = themes[theme] || themes["Default"];

  console.log("Selected theme data:", selectedTheme);

  for (const [varName, color] of Object.entries(selectedTheme)) {
    root.style.setProperty(varName, color);
  }

  const currentSectionBg =
    selectedTheme["--section-bg"] || themes["Default"]["--section-bg"];
  const modal = document.querySelector(".modal");
  if (modal) modal.style.background = currentSectionBg;

  const body = document.body;
  body.classList.remove("theme-light-mode", "theme-dark-mode");

  if (theme === "Light Mode") {
    body.classList.add("theme-light-mode");
  } else if (theme === "Dark Mode") {
    body.classList.add("theme-dark-mode");
  }

  localStorage.setItem("dashboard-theme", theme);

  console.log("Theme applied and saved:", theme);

  document.body.style.display = "none";
  document.body.offsetHeight;
  document.body.style.display = "";
}

class EventCalendar {
  constructor() {
    this.currentDate = new Date();
    this.currentView = "month";
    this.events = [];
    this.init();
  }

  async init() {
    await this.loadEvents();
    this.setupEventListeners();
    this.renderCalendar();
  }

  async loadEvents() {
    try {
      console.log("Loading events from fetch_announcements.php...");
      const response = await fetch("../../Connection/Announcement/FetchAnnouncement.php");
      console.log("Response status:", response.status);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      console.log("Fetched data:", data);

      if (data.success) {
        this.events = data.announcements.map((announcement) => {
          console.log(
            "Processing announcement:",
            announcement.title,
            "with date:",
            announcement.date
          );
          return {
            id: announcement.id,
            title: announcement.title,
            description: announcement.message,
            date: announcement.date || this.formatDate(new Date()),
            time: announcement.time || "",
            type: "announcement",
            color: "#0c27be",
          };
        });
        console.log("Processed events:", this.events);
        this.renderCalendar();
      } else {
        console.error("Failed to load events:", data.message);
      }
    } catch (error) {
      console.error("Error loading events:", error);
    }
  }

  setupEventListeners() {
    document.getElementById("prev-month")?.addEventListener("click", () => {
      this.navigateMonth(-1);
    });

    document.getElementById("next-month")?.addEventListener("click", () => {
      this.navigateMonth(1);
    });

    document.querySelectorAll(".view-btn").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        this.setView(e.target.dataset.view);
      });
    });
  }

  navigateMonth(direction) {
    this.currentDate.setMonth(this.currentDate.getMonth() + direction);
    this.renderCalendar();
  }

  setView(view) {
    this.currentView = view;

    document.querySelectorAll(".view-btn").forEach((btn) => {
      btn.classList.remove("active");
    });
    document.querySelector(`[data-view="${view}"]`)?.classList.add("active");

    this.renderCalendar();

    if (view === "week" || view === "list") {
      setTimeout(() => {
        window.scrollTo({
          top: document.body.scrollHeight,
          behavior: "smooth",
        });
      }, 100);
    }
  }

  renderCalendar() {
    this.updateHeader();

    if (this.currentView === "month") {
      this.renderMonthView();
    } else if (this.currentView === "week") {
      this.renderWeekView();
    } else if (this.currentView === "day") {
      this.renderDayView();
    } else if (this.currentView === "list") {
      this.renderListView();
    }
  }

  updateHeader() {
    const header = document.getElementById("current-month");
    if (header) {
      const options = { year: "numeric", month: "long" };
      header.textContent = this.currentDate.toLocaleDateString(
        "en-US",
        options
      );
    }
  }

  renderMonthView() {
    const grid = document.getElementById("calendar-grid");
    if (!grid) return;

    const year = this.currentDate.getFullYear();
    const month = this.currentDate.getMonth();

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());

    let html = `
      <div class="calendar-weekdays">
        <div class="weekday">Sun</div>
        <div class="weekday">Mon</div>
        <div class="weekday">Tue</div>
        <div class="weekday">Wed</div>
        <div class="weekday">Thu</div>
        <div class="weekday">Fri</div>
        <div class="weekday">Sat</div>
      </div>
      <div class="calendar-days">
    `;

    for (let i = 0; i < 42; i++) {
      const currentDate = new Date(startDate);
      currentDate.setDate(startDate.getDate() + i);

      const isCurrentMonth = currentDate.getMonth() === month;
      const isToday = this.isToday(currentDate);
      const dayEvents = this.getEventsForDate(currentDate);

      const dayClass = `calendar-day ${
        isCurrentMonth ? "current-month" : "other-month"
      } ${isToday ? "today" : ""}`;

      html += `
        <div class="${dayClass}" data-date="${this.formatDate(currentDate)}">
          <div class="day-number">${currentDate.getDate()}</div>
          ${
            dayEvents.length > 0
              ? `
            <div class="event-dots-container">
              ${dayEvents
                .map(
                  (event) => `
                <div class="event-dot" title="${
                  event.title
                } - ${event.description.substring(0, 50)}${
                    event.description.length > 50 ? "..." : ""
                  }"></div>
              `
                )
                .join("")}
              ${
                this.events.filter(
                  (event) =>
                    this.formatDate(new Date(event.date)) ===
                    this.formatDate(currentDate)
                ).length > 5
                  ? `<div class="more-events-indicator" title="More events available">+${
                      this.events.filter(
                        (event) =>
                          this.formatDate(new Date(event.date)) ===
                          this.formatDate(currentDate)
                      ).length - 5
                    }</div>`
                  : ""
              }
            </div>
          `
              : ""
          }
        </div>
      `;
    }

    html += "</div>";
    grid.innerHTML = html;

    grid.querySelectorAll(".calendar-day").forEach((day) => {
      day.addEventListener("click", () => {
        const date = day.dataset.date;
        this.showDayEvents(date);
      });
    });
  }

  renderWeekView() {
    const grid = document.getElementById("calendar-grid");
    if (!grid) return;

    const weekStart = this.getWeekStart(this.currentDate);
    let html = '<div class="week-view">';

    for (let i = 0; i < 7; i++) {
      const currentDate = new Date(weekStart);
      currentDate.setDate(weekStart.getDate() + i);

      const dayEvents = this.getEventsForDate(currentDate);
      const isToday = this.isToday(currentDate);

      html += `
        <div class="week-day ${isToday ? "today" : ""}">
          <div class="day-header">
            <div class="day-name">${currentDate.toLocaleDateString("en-US", {
              weekday: "short",
            })}</div>
            <div class="day-number">${currentDate.getDate()}</div>
          </div>
          <div class="day-events">
            ${dayEvents
              .map(
                (event) => `
              <div class="week-event" style="border-left: 3px solid ${
                event.color
              }">
                <div class="event-title">${event.title}</div>
                ${
                  event.time
                    ? `<div class="event-time">${event.time}</div>`
                    : ""
                }
              </div>
            `
              )
              .join("")}
          </div>
        </div>
      `;
    }

    html += "</div>";
    grid.innerHTML = html;
  }

  renderDayView() {
    const grid = document.getElementById("calendar-grid");
    if (!grid) return;

    const dayEvents = this.getEventsForDate(this.currentDate);

    let html = `
      <div class="day-view">
        <div class="day-header">
          <h3>${this.currentDate.toLocaleDateString("en-US", {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric",
          })}</h3>
        </div>
        <div class="day-events">
          ${
            dayEvents.length > 0
              ? dayEvents
                  .map(
                    (event) => `
            <div class="day-event" style="border-left: 4px solid ${
              event.color
            }">
              <div class="event-header">
                <h4 class="event-title">${event.title}</h4>
                ${
                  event.time
                    ? `<span class="event-time">${event.time}</span>`
                    : ""
                }
              </div>
              <div class="event-description">${event.description}</div>
            </div>
          `
                  )
                  .join("")
              : '<div class="no-events">No events for this day</div>'
          }
        </div>
      </div>
    `;

    grid.innerHTML = html;
  }

  renderListView() {
    const grid = document.getElementById("calendar-grid");
    if (!grid) return;

    const sortedEvents = [...this.events].sort(
      (a, b) => new Date(a.date) - new Date(b.date)
    );

    let html = `
      <div class="list-view">
        <h3>All Events</h3>
        <div class="events-list">
          ${
            sortedEvents.length > 0
              ? sortedEvents
                  .map(
                    (event) => `
            <div class="list-event" style="border-left: 4px solid ${
              event.color
            }">
              <div class="event-date">${this.formatDate(
                new Date(event.date)
              )}</div>
              <div class="event-content">
                <h4 class="event-title">${event.title}</h4>
                <div class="event-description">${event.description}</div>
                ${
                  event.time
                    ? `<div class="event-time">${event.time}</div>`
                    : ""
                }
              </div>
            </div>
          `
                  )
                  .join("")
              : '<div class="no-events">No events scheduled</div>'
          }
        </div>
      </div>
    `;

    grid.innerHTML = html;

    const listView = grid.querySelector(".list-view");
    if (listView) {
      listView.scrollTop = 0;
    }
  }

  getEventsForDate(date) {
    const dateStr = this.formatDate(date);
    const events = this.events.filter((event) => event.date === dateStr);
    return events.slice(0, 5);
  }

  showDayEvents(date) {
    const events = this.getEventsForDate(new Date(date));

    if (events.length === 0) {
      return;
    }

    const modal = document.getElementById("event-modal-overlay");
    const content = document.getElementById("event-preview-content");

    if (modal && content) {
      content.innerHTML = `
        <div class="events-preview-container">
          ${events
            .map(
              (event, index) => `
            <div class="event-preview-card">
              <button class="delete-btn" onclick="eventCalendar.deleteEvent('${
                event.id
              }', '${event.date}')" title="Delete announcement">×</button>
              <div class="event-preview-title">${event.title}</div>
              <div class="event-preview-message">${event.description}</div>
              <div class="event-preview-meta">
                <span class="event-preview-date">${this.formatDate(
                  new Date(event.date)
                )}</span>
                ${
                  event.time
                    ? `<span class="event-preview-time">${event.time}</span>`
                    : ""
                }
              </div>
            </div>
          `
            )
            .join("")}
        </div>
      `;

      modal.style.display = "flex";
    }
  }

  getWeekStart(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day;
    return new Date(d.setDate(diff));
  }

  isToday(date) {
    const today = new Date();
    return date.toDateString() === today.toDateString();
  }

  formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  }

  async deleteEvent(eventId, eventDate) {
    const confirmed = await this.showConfirmationModal(
      " Delete Announcement",
      "Are you sure you want to delete this announcement?"
    );

    if (!confirmed) {
      return;
    }

    try {
      console.log(
        "Deleting announcement with ID:",
        eventId,
        "Date:",
        eventDate
      );

      const response = await fetch("../../Connection/Announcement/DeleteAnnouncement.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          id: eventId,
          date: eventDate,
        }),
      });

      console.log("Delete response status:", response.status);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      console.log("Delete response:", result);

      if (result.success) {
        await this.loadEvents();
        this.renderCalendar();

        const modal = document.getElementById("event-modal-overlay");
        if (modal) {
          modal.style.display = "none";
        }
      }
    } catch (error) {
      console.error("Error deleting announcement:", error);
    }
  }
  
  showConfirmationModal(title, message) {
    return new Promise((resolve) => {
      const overlay = document.createElement("div");
      overlay.className = "confirmation-modal-overlay";

      const modal = document.createElement("div");
      modal.className = "confirmation-modal";
      modal.innerHTML = `
        <h3>${title}</h3>
        <p>${message}</p>
        <div class="confirmation-modal-buttons">
          <button class="confirmation-btn cancel">Cancel</button>
          <button class="confirmation-btn delete">Delete</button>
        </div>
      `;

      overlay.appendChild(modal);
      document.body.appendChild(overlay);

      const cancelBtn = modal.querySelector(".confirmation-btn.cancel");
      const deleteBtn = modal.querySelector(".confirmation-btn.delete");

      const closeModal = (result) => {
        overlay.style.animation = "fadeIn 0.3s ease-out reverse";
        modal.style.animation = "slideInUp 0.3s ease-out reverse";
        setTimeout(() => {
          if (overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
          }
          resolve(result);
        }, 300);
      };

      cancelBtn.addEventListener("click", () => closeModal(false));

      deleteBtn.addEventListener("click", () => closeModal(true));

      overlay.addEventListener("click", (e) => {
        if (e.target === overlay) {
          closeModal(false);
        }
      });

      const handleEscape = (e) => {
        if (e.key === "Escape") {
          document.removeEventListener("keydown", handleEscape);
          closeModal(false);
        }
      };
      document.addEventListener("keydown", handleEscape);
    });
  }
}

let eventCalendar;

document.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  eventCalendar = new EventCalendar();

  const modal = document.getElementById("event-modal-overlay");
  const cancelBtn = document.getElementById("cancel-event-btn");

  if (modal) {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.style.display = "none";
      }
    });
  }

  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
      modal.style.display = "none";
    });
  }
});
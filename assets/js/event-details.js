// Calendar Logic
function addToCalendar() {
  const event = {
    title: "INNOVENTURE TOUR 2.0",
    description:
      "One day of exploration, discovery, and fun! Kids visit real tech companies, ask questions, try hands-on activities, and take home awesome souvenirs.",
    location: "Ghana Labs",
    startTime: "20260227T090000",
    endTime: "20260227T140000",
  };

  const icsContent = [
    "BEGIN:VCALENDAR",
    "VERSION:2.0",
    "PRODID:-//ChildInTech//Events//EN",
    "BEGIN:VEVENT",
    `SUMMARY:${event.title}`,
    `DESCRIPTION:${event.description}`,
    `LOCATION:${event.location}`,
    `DTSTART:${event.startTime}`,
    `DTEND:${event.endTime}`,
    "BEGIN:VALARM",
    "TRIGGER:-PT24H",
    "ACTION:DISPLAY",
    `DESCRIPTION:Reminder: ${event.title} is tomorrow!`,
    "END:VALARM",
    "END:VEVENT",
    "END:VCALENDAR",
  ].join("\r\n");

  const blob = new Blob([icsContent], {
    type: "text/calendar;charset=utf-8",
  });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = "innoventure_tour.ics";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Attach to Hero Button
const heroBtn = document.getElementById("heroRegisterBtn");
if (heroBtn) {
  heroBtn.addEventListener("click", addToCalendar);
}

// Attach to Nav Button
const navBtn = document.getElementById("navRegisterBtn");
if (navBtn) {
  navBtn.addEventListener("click", addToCalendar);
}

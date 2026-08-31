import { useRef, useEffect, useState } from "react";

const reviews = [
  {
    name: "Sophie M.",
    date: "28 août 2026",
    text: "Écran de mon iPhone 15 remplacé en 45 minutes, comme neuf ! Personnel très sympathique et professionnel.",
    stars: 5,
  },
  {
    name: "Karim B.",
    date: "27 août 2026",
    text: "Batterie de mon Samsung S23 changée rapidement. Le téléphone tient toute la journée maintenant. Merci Fatfix !",
    stars: 5,
  },
  {
    name: "Julie D.",
    date: "26 août 2026",
    text: "Mon MacBook avait un problème de surchauffe. Diagnostic gratuit et réparation le jour même. Je recommande à 100%.",
    stars: 5,
  },
  {
    name: "Thomas L.",
    date: "25 août 2026",
    text: "Excellent rapport qualité/prix. Mon iPad est réparé et ils m'ont même mis un film de protection offert.",
    stars: 5,
  },
  {
    name: "Amira K.",
    date: "24 août 2026",
    text: "Service rapide et soigné. J'ai récupéré mon téléphone en moins d'une heure. La garantie d'un an me rassure.",
    stars: 5,
  },
  {
    name: "Lucas R.",
    date: "23 août 2026",
    text: "Réparation de la vitre arrière de mon iPhone 14 Pro. Travail impeccable, on ne voit plus rien !",
    stars: 4,
  },
  {
    name: "Fatima Z.",
    date: "22 août 2026",
    text: "Incroyable le travail, simple et efficace. Très gentil et respectueux, top !",
    stars: 5,
  },
  {
    name: "David P.",
    date: "21 août 2026",
    text: "J'ai envoyé mon téléphone par la poste, réparé en 3 jours. Très pratique quand on n'habite pas à côté.",
    stars: 5,
  },
];

function Stars({ count }: { count: number }) {
  return (
    <div className="flex gap-0.5" aria-label={`${count} étoiles sur 5`}>
      {Array.from({ length: 5 }).map((_, i) => (
        <svg
          key={i}
          className={`h-4 w-4 ${i < count ? "text-warning fill-warning" : "text-border fill-border"}`}
          viewBox="0 0 20 20"
        >
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
      ))}
    </div>
  );
}

export default function ReviewCarousel() {
  const scrollRef = useRef<HTMLDivElement>(null);
  const [isPaused, setIsPaused] = useState(false);

  useEffect(() => {
    const el = scrollRef.current;
    if (!el) return;
    let frame: number;
    const speed = 0.4;
    function step() {
      if (!isPaused && el) {
        el.scrollLeft += speed;
        if (el.scrollLeft >= el.scrollWidth - el.clientWidth - 1) {
          el.scrollLeft = 0;
        }
      }
      frame = requestAnimationFrame(step);
    }
    frame = requestAnimationFrame(step);
    return () => cancelAnimationFrame(frame);
  }, [isPaused]);

  return (
    <div
      ref={scrollRef}
      onMouseEnter={() => setIsPaused(true)}
      onMouseLeave={() => setIsPaused(false)}
      onTouchStart={() => setIsPaused(true)}
      onTouchEnd={() => setIsPaused(false)}
      className="flex gap-5 overflow-x-auto scroll-smooth pb-4"
      style={{ scrollbarWidth: "none", msOverflowStyle: "none" }}
    >
      {reviews.map((r, i) => (
        <article
          key={i}
          className="flex-shrink-0 w-[320px] rounded-2xl border border-border bg-white p-6 flex flex-col gap-4 transition-shadow hover:shadow-md"
        >
          <Stars count={r.stars} />
          <p className="text-sm text-text-light leading-relaxed line-clamp-4">
            « {r.text} »
          </p>
          <div className="mt-auto flex items-center gap-3 pt-3 border-t border-border">
            <div className="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">
              {r.name.split(" ").map(n => n[0]).join("")}
            </div>
            <div>
              <div className="font-display font-semibold text-sm text-text">{r.name}</div>
              <div className="text-xs text-text-muted">{r.date}</div>
            </div>
          </div>
        </article>
      ))}
    </div>
  );
}

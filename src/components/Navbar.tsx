import { useState, useEffect } from "react";

const links = [
  { label: "Réparations", href: "/reparations" },
  { label: "Prix", href: "/prix" },
  { label: "Reconditionnés", href: "/reconditionnes" },
  { label: "Boutiques", href: "/boutiques" },
  { label: "À propos", href: "/a-propos" },
  { label: "Contact", href: "/contact" },
];

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    document.body.style.overflow = menuOpen ? "hidden" : "";
    return () => { document.body.style.overflow = ""; };
  }, [menuOpen]);

  return (
    <>
      {/* Trust bar — scrolling marquee style */}
      <div className="bg-bg-dark text-white text-xs py-2 hidden md:block">
        <div className="mx-auto max-w-7xl px-6 flex items-center justify-between">
          <div className="flex items-center gap-5 flex-wrap">
            {/* Google rating */}
            <span className="flex items-center gap-1.5">
              <svg className="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
              </svg>
              <span className="font-semibold">4,9</span>
              <span className="text-white/80">· 2 000+ avis Google</span>
            </span>
            <span className="text-white/30">·</span>
            <span className="flex items-center gap-1.5 text-white/90">
              <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
              </svg>
              Jusqu'à 1 an de garantie
            </span>
            <span className="text-white/30">·</span>
            <span className="flex items-center gap-1.5 text-white/90">
              <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Prêt en 60 min
            </span>
            <span className="text-white/30">·</span>
            <span className="text-white/90">Joignable 7j/7</span>
          </div>
          <a href="tel:+3222194916" className="text-white/90 hover:text-white transition-colors flex items-center gap-1.5 font-medium">
            <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
            </svg>
            02 219 49 16
          </a>
        </div>
      </div>

      {/* Main nav */}
      <header
        className={`sticky top-0 z-50 transition-all duration-300 ${
          scrolled
            ? "bg-white/95 backdrop-blur-md shadow-sm border-b border-border"
            : "bg-white border-b border-border"
        }`}
      >
        <nav className="mx-auto flex max-w-7xl items-center justify-between px-6 py-3 lg:px-8">
          <a href="/" className="flex items-center select-none">
            <img src="/images/logo-fastfix.png" alt="FastFix" className="h-10" />
          </a>

          <ul className="hidden lg:flex items-center gap-6">
            {links.map((l) => (
              <li key={l.href}>
                <a href={l.href} className="text-sm font-medium text-text-light hover:text-primary transition-colors">
                  {l.label}
                </a>
              </li>
            ))}
          </ul>

          <div className="hidden lg:flex items-center gap-3">
            <a
              href="tel:+3222194916"
              className="flex h-10 w-10 items-center justify-center rounded-full hover:bg-bg-alt transition-colors text-text-muted hover:text-primary"
              aria-label="Appeler"
            >
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
              </svg>
            </a>
            <a
              href="/rdv"
              className="inline-flex items-center gap-2 rounded-full bg-primary hover:bg-primary-dark px-5 py-2.5 text-sm font-semibold text-white transition-colors shadow-sm"
            >
              Prendre rendez-vous
            </a>
          </div>

          <button
            onClick={() => setMenuOpen(!menuOpen)}
            className="lg:hidden relative z-50 flex h-10 w-10 items-center justify-center"
            aria-label={menuOpen ? "Fermer le menu" : "Ouvrir le menu"}
          >
            <div className="flex flex-col gap-1.5">
              <span className={`block h-0.5 w-6 rounded transition-all duration-300 origin-center ${menuOpen ? "bg-text rotate-45 translate-y-[4px]" : "bg-text-light"}`} />
              <span className={`block h-0.5 w-6 rounded transition-all duration-300 ${menuOpen ? "opacity-0 scale-0" : "bg-text-light"}`} />
              <span className={`block h-0.5 w-6 rounded transition-all duration-300 origin-center ${menuOpen ? "bg-text -rotate-45 -translate-y-[4px]" : "bg-text-light"}`} />
            </div>
          </button>
        </nav>

        <div
          className={`fixed inset-0 bg-white z-40 flex flex-col items-center justify-center gap-5 transition-all duration-300 lg:hidden ${
            menuOpen ? "opacity-100 pointer-events-auto" : "opacity-0 pointer-events-none"
          }`}
        >
          {links.map((l) => (
            <a key={l.href} href={l.href} onClick={() => setMenuOpen(false)}
               className="font-display text-xl font-semibold text-text hover:text-primary transition-colors">
              {l.label}
            </a>
          ))}
          <a href="/envoi" onClick={() => setMenuOpen(false)}
             className="font-display text-xl font-semibold text-text hover:text-primary transition-colors">
            Envoi postal
          </a>
          <a href="/garantie" onClick={() => setMenuOpen(false)}
             className="font-display text-xl font-semibold text-text hover:text-primary transition-colors">
            Garantie
          </a>
          <a href="/rdv" onClick={() => setMenuOpen(false)}
             className="mt-4 rounded-full bg-primary px-8 py-3 text-lg font-semibold text-white">
            Prendre RDV
          </a>
        </div>
      </header>
    </>
  );
}

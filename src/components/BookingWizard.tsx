import { useState, useMemo } from "react";

/* ─── DATA ─── */
const brands = [
  { id: "apple", label: "APPLE" },
  { id: "samsung", label: "SAMSUNG" },
  { id: "google", label: "GOOGLE" },
];

const deviceTypes: Record<string, { id: string; label: string }[]> = {
  apple: [
    { id: "iphone", label: "IPHONE" },
    { id: "ipad", label: "IPAD" },
    { id: "apple-watch", label: "APPLE WATCH" },
    { id: "macbook", label: "MACBOOK" },
  ],
  samsung: [
    { id: "galaxy-s", label: "GALAXY S" },
    { id: "galaxy-a", label: "GALAXY A" },
    { id: "galaxy-tab", label: "GALAXY TAB" },
    { id: "galaxy-z", label: "GALAXY Z" },
  ],
  google: [
    { id: "pixel", label: "PIXEL" },
    { id: "pixel-tablet", label: "PIXEL TABLET" },
  ],
};

interface DeviceModel {
  name: string;
  models: string;
  img: string;
}

const devices: Record<string, DeviceModel[]> = {
  iphone: [
    { name: "iPhone 17 Pro Max", models: "A3526, A3527", img: "/images/devices/iphone-17-pro.webp" },
    { name: "iPhone 17 Pro", models: "A3518, A3523, A3523", img: "/images/devices/iphone-17-pro.webp" },
    { name: "iPhone 17", models: "A3520, A3526, A3518", img: "/images/devices/iphone-17.webp" },
    { name: "iPhone 16 Pro Max", models: "A3396, A3004, A3295", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone 16 Pro", models: "A3293, A3083, A3293", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone 16 Plus", models: "A3190, A3082, A3288", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone 16", models: "A3287, A3081, A3286", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone 15 Pro Max", models: "A2849, A3105, A3106", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone 15 Pro", models: "A2848, A3101, A3103", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone 15 Plus", models: "A2884, A2847, A3093", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone 15", models: "A2846, A3090, A3089", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone 14 Pro Max", models: "A2894, A2651, A2893", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone 14 Pro", models: "A2890, A2650, A2889", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone 14 Plus", models: "A2886, A2632, A2885", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone 14", models: "A2882, A2649, A2881", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone 13 Pro Max", models: "A2643, A2484, A2641", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone 13 Pro", models: "A2638, A2483, A2636", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone 13", models: "A2633, A2482, A2631", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone 12 Pro Max", models: "A2411, A2342, A2410", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone 12 Pro", models: "A2407, A2341, A2406", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone 12", models: "A2403, A2172, A2402", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone 11 Pro Max", models: "A2218, A2161, A2220", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone 11 Pro", models: "A2215, A2160, A2217", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone 11", models: "A2221, A2111, A2223", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone XS Max", models: "A2097, A1920, A2100", img: "/images/devices/iphone-16-pro-max.webp" },
    { name: "iPhone XS", models: "A2097, A1920, A2100", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone XR", models: "A2105, A1984, A2107", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone X", models: "A1865, A1901, A1902", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone 8 Plus", models: "A1864, A1897, A1898", img: "/images/devices/iphone-16.webp" },
    { name: "iPhone 8", models: "A1863, A1905, A1906", img: "/images/devices/iphone-16.webp" },
  ],
  ipad: [
    { name: "iPad Pro 13\" M5", models: "A3456", img: "/images/devices/ipad-pro.webp" },
    { name: "iPad Pro 11\" M5", models: "A3455", img: "/images/devices/ipad-pro.webp" },
    { name: "iPad Pro 13\" M4", models: "A2926, A2930", img: "/images/devices/ipad-pro.webp" },
    { name: "iPad Pro 11\" M4", models: "A2836, A2837", img: "/images/devices/ipad-pro.webp" },
    { name: "iPad Air M3", models: "A3006", img: "/images/devices/ipad-pro.webp" },
    { name: "iPad Air M2", models: "A2898, A2899", img: "/images/devices/ipad-pro.webp" },
    { name: "iPad 10e gén.", models: "A2696, A2757", img: "/images/devices/ipad-pro.webp" },
    { name: "iPad Mini 7", models: "A2993, A2994", img: "/images/devices/ipad-pro.webp" },
  ],
  "apple-watch": [
    { name: "Apple Watch Ultra 2", models: "A2703", img: "/images/devices/apple-watch.webp" },
    { name: "Apple Watch Series 10", models: "A3000", img: "/images/devices/apple-watch.webp" },
    { name: "Apple Watch Series 9", models: "A2978", img: "/images/devices/apple-watch.webp" },
    { name: "Apple Watch SE (2e)", models: "A2725", img: "/images/devices/apple-watch.webp" },
  ],
  macbook: [
    { name: "MacBook Air 15\" M4", models: "2025", img: "/images/devices/macbook-air.webp" },
    { name: "MacBook Air 13\" M4", models: "2025", img: "/images/devices/macbook-air.webp" },
    { name: "MacBook Pro 16\" M4 Pro/Max", models: "2024", img: "/images/devices/macbook-air.webp" },
    { name: "MacBook Pro 14\" M4", models: "2024", img: "/images/devices/macbook-air.webp" },
    { name: "MacBook Air 15\" M3", models: "2024", img: "/images/devices/macbook-air.webp" },
    { name: "MacBook Air 13\" M3", models: "2024", img: "/images/devices/macbook-air.webp" },
  ],
  "galaxy-s": [
    { name: "Galaxy S26 Ultra", models: "SM-S938B", img: "/images/devices/galaxy-s26-ultra.webp" },
    { name: "Galaxy S26+", models: "SM-S936B", img: "/images/devices/galaxy-s26.webp" },
    { name: "Galaxy S26", models: "SM-S931B", img: "/images/devices/galaxy-s26.webp" },
    { name: "Galaxy S25 Ultra", models: "SM-S928B", img: "/images/devices/galaxy-s26-ultra.webp" },
    { name: "Galaxy S25+", models: "SM-S926B", img: "/images/devices/galaxy-s26.webp" },
    { name: "Galaxy S25", models: "SM-S921B", img: "/images/devices/galaxy-s26.webp" },
    { name: "Galaxy S24 Ultra", models: "SM-S928B", img: "/images/devices/galaxy-s26-ultra.webp" },
    { name: "Galaxy S24+", models: "SM-S926B", img: "/images/devices/galaxy-s24.webp" },
    { name: "Galaxy S24", models: "SM-S921B", img: "/images/devices/galaxy-s24.webp" },
    { name: "Galaxy S23 Ultra", models: "SM-S918B", img: "/images/devices/galaxy-s26-ultra.webp" },
    { name: "Galaxy S23+", models: "SM-S916B", img: "/images/devices/galaxy-s24.webp" },
    { name: "Galaxy S23", models: "SM-S911B", img: "/images/devices/galaxy-s24.webp" },
  ],
  "galaxy-a": [
    { name: "Galaxy A56", models: "SM-A566B", img: "/images/devices/galaxy-s24.webp" },
    { name: "Galaxy A36", models: "SM-A366B", img: "/images/devices/galaxy-s24.webp" },
    { name: "Galaxy A16", models: "SM-A166B", img: "/images/devices/galaxy-s24.webp" },
    { name: "Galaxy A55", models: "SM-A556B", img: "/images/devices/galaxy-s24.webp" },
    { name: "Galaxy A35", models: "SM-A356B", img: "/images/devices/galaxy-s24.webp" },
    { name: "Galaxy A15", models: "SM-A156B", img: "/images/devices/galaxy-s24.webp" },
  ],
  "galaxy-tab": [
    { name: "Galaxy Tab S10 Ultra", models: "SM-X920", img: "/images/devices/galaxy-tab.webp" },
    { name: "Galaxy Tab S10+", models: "SM-X820", img: "/images/devices/galaxy-tab.webp" },
    { name: "Galaxy Tab S9 FE", models: "SM-X510", img: "/images/devices/galaxy-tab.webp" },
  ],
  "galaxy-z": [
    { name: "Galaxy Z Fold 6", models: "SM-F956B", img: "/images/devices/galaxy-s24.webp" },
    { name: "Galaxy Z Flip 6", models: "SM-F741B", img: "/images/devices/galaxy-s24.webp" },
    { name: "Galaxy Z Fold 5", models: "SM-F946B", img: "/images/devices/galaxy-s24.webp" },
    { name: "Galaxy Z Flip 5", models: "SM-F731B", img: "/images/devices/galaxy-s24.webp" },
  ],
  pixel: [
    { name: "Pixel 10 Pro", models: "2025", img: "/images/devices/pixel-10-pro.webp" },
    { name: "Pixel 10", models: "2025", img: "/images/devices/pixel-10-pro.webp" },
    { name: "Pixel 9 Pro XL", models: "2024", img: "/images/devices/pixel-10-pro.webp" },
    { name: "Pixel 9 Pro", models: "2024", img: "/images/devices/pixel-10-pro.webp" },
    { name: "Pixel 9", models: "2024", img: "/images/devices/pixel-10-pro.webp" },
    { name: "Pixel 8 Pro", models: "2023", img: "/images/devices/pixel-10-pro.webp" },
    { name: "Pixel 8", models: "2023", img: "/images/devices/pixel-10-pro.webp" },
  ],
  "pixel-tablet": [
    { name: "Pixel Tablet", models: "2023", img: "/images/devices/pixel-10-pro.webp" },
  ],
};

interface Repair {
  name: string;
  desc: string;
  price: number;
  oldPrice?: number;
  badge?: string;
  badgeColor?: string;
  features: string[];
  time: string;
  warranty: string;
  attention?: string;
}

interface RepairCategory {
  icon: string;
  title: string;
  repairs: Repair[];
}

const repairsByType: Record<string, RepairCategory[]> = {
  iphone: [
    {
      icon: "📱", title: "Écran et vitre", repairs: [
        { name: "Remplacement de la vitre", desc: "La vitre est fissurée, mais l'écran fonctionne encore parfaitement.", price: 119, oldPrice: 149, badge: "CHOIX LE PLUS POPULAIRE", badgeColor: "red", features: ["Conservation de votre écran original", "Qualité identique à un écran neuf", "Moins cher qu'un remplacement complet"], time: "90 min", warranty: "6 mois de garantie", attention: "L'image et le tactile doivent fonctionner à 100%." },
        { name: "Écran complet OLED original", desc: "Si l'écran ne fonctionne plus ou présente des taches ou des lignes.", price: 499, oldPrice: 549, badge: "QUALITÉ PREMIUM", badgeColor: "green", features: ["Qualité Apple 100% originale", "Module écran complet neuf", "Résultat comme neuf garanti"], time: "60 min", warranty: "6 mois de garantie" },
      ]
    },
    {
      icon: "🔋", title: "Batterie & Charge", repairs: [
        { name: "Batterie", desc: "Le téléphone s'éteint, se vide vite ou le pourcentage n'est pas correct.", price: 129, oldPrice: 159, badge: "100% ORIGINAL", badgeColor: "red", features: ["Votre appareil tient toute la journée", "Capacité et durée de vie d'origine", "Installation professionnelle"], time: "60 min", warranty: "6 mois de garantie" },
        { name: "Port de charge", desc: "Le câble de charge tient mal ou l'appareil charge lentement.", price: 59, badge: "RÉPARATION RAPIDE", badgeColor: "green", features: ["Le câble se clipse à nouveau fermement", "Charge sûre et stable", "Testé après montage"], time: "60 min", warranty: "6 mois de garantie" },
      ]
    },
    {
      icon: "🔍", title: "Diagnostic & Logiciel", repairs: [
        { name: "Diagnostic", desc: "Vous ne savez pas ce qui est cassé, ou l'appareil ne fonctionne plus.", price: 0, badge: "DIAGNOSTIC CLAIR", badgeColor: "green", features: ["Gratuit en cas de réparation", "Estimation transparente", "Résultat en 30 minutes"], time: "30 min", warranty: "Sans engagement", attention: "Si l'appareil n'est pas réparable, le diagnostic coûte 30 EUR." },
      ]
    },
    {
      icon: "🔧", title: "Vitre arrière & Châssis", repairs: [
        { name: "Vitre arrière", desc: "La face arrière de votre iPhone est fissurée.", price: 89, oldPrice: 109, badge: "RÉPARATION POPULAIRE", badgeColor: "red", features: ["Vitre arrière identique à l'original", "Adhésif étanche remplacé", "Finition impeccable"], time: "90 min", warranty: "6 mois de garantie" },
      ]
    },
    {
      icon: "💧", title: "Dégât des eaux", repairs: [
        { name: "Traitement dégât des eaux", desc: "Votre appareil est tombé dans l'eau ou a été exposé à l'humidité.", price: 79, features: ["Nettoyage ultrasonique complet", "Séchage professionnel", "Diagnostic de tous les composants"], time: "24-48h", warranty: "Selon résultat" },
      ]
    },
  ],
  ipad: [
    {
      icon: "📱", title: "Écran", repairs: [
        { name: "Écran complet iPad", desc: "L'écran est fissuré ou ne fonctionne plus.", price: 199, oldPrice: 249, badge: "RÉPARATION COURANTE", badgeColor: "red", features: ["Écran de qualité originale", "Tactile parfait", "Garantie incluse"], time: "120 min", warranty: "6 mois de garantie" },
      ]
    },
    {
      icon: "🔋", title: "Batterie", repairs: [
        { name: "Batterie iPad", desc: "L'iPad ne tient plus la journée.", price: 149, features: ["Batterie neuve", "Capacité d'origine restaurée", "Installation professionnelle"], time: "90 min", warranty: "6 mois de garantie" },
      ]
    },
  ],
};

// Default repairs for types without specific data
const defaultRepairs: RepairCategory[] = [
  {
    icon: "📱", title: "Écran", repairs: [
      { name: "Remplacement écran", desc: "L'écran est fissuré ou ne fonctionne plus.", price: 149, badge: "RÉPARATION COURANTE", badgeColor: "red", features: ["Écran de qualité", "Tactile parfait", "Garantie incluse"], time: "60-120 min", warranty: "6 mois de garantie" },
    ]
  },
  {
    icon: "🔋", title: "Batterie", repairs: [
      { name: "Remplacement batterie", desc: "L'appareil se décharge rapidement.", price: 99, features: ["Batterie neuve", "Capacité restaurée", "Installation pro"], time: "60 min", warranty: "6 mois de garantie" },
    ]
  },
  {
    icon: "🔍", title: "Diagnostic", repairs: [
      { name: "Diagnostic complet", desc: "Vous ne savez pas ce qui ne va pas.", price: 0, badge: "GRATUIT*", badgeColor: "green", features: ["Gratuit si réparation", "Estimation transparente", "Résultat rapide"], time: "30 min", warranty: "Sans engagement", attention: "30 EUR si l'appareil n'est pas réparé." },
    ]
  },
];

function getRepairs(deviceType: string): RepairCategory[] {
  return repairsByType[deviceType] || defaultRepairs;
}

/* ─── COMPONENT ─── */
export default function BookingWizard() {
  const [step, setStep] = useState(1);
  const [brand, setBrand] = useState("apple");
  const [deviceType, setDeviceType] = useState("iphone");
  const [selectedDevice, setSelectedDevice] = useState<DeviceModel | null>(null);
  const [selectedRepairs, setSelectedRepairs] = useState<Repair[]>([]);
  const [search, setSearch] = useState("");
  const [clientType, setClientType] = useState<"particulier" | "entreprise">("particulier");
  const [formData, setFormData] = useState({ name: "", phone: "", email: "", notes: "" });
  const [submitted, setSubmitted] = useState(false);

  const currentDeviceTypes = deviceTypes[brand] || [];
  const currentDevices = devices[deviceType] || [];
  const filteredDevices = useMemo(() => {
    if (!search.trim()) return currentDevices;
    const q = search.toLowerCase();
    return currentDevices.filter(
      (d) => d.name.toLowerCase().includes(q) || d.models.toLowerCase().includes(q)
    );
  }, [currentDevices, search]);

  const total = selectedRepairs.reduce((s, r) => s + r.price, 0);

  const handleBrandChange = (b: string) => {
    setBrand(b);
    const types = deviceTypes[b];
    setDeviceType(types?.[0]?.id || "");
    setSearch("");
  };

  const handleDeviceTypeChange = (t: string) => {
    setDeviceType(t);
    setSearch("");
  };

  const toggleRepair = (repair: Repair) => {
    setSelectedRepairs((prev) =>
      prev.find((r) => r.name === repair.name)
        ? prev.filter((r) => r.name !== repair.name)
        : [...prev, repair]
    );
  };

  const handleSubmit = () => {
    setSubmitted(true);
    setStep(4);
  };

  /* ─── STEP INDICATOR ─── */
  const steps = [
    { n: 1, label: "Appareil" },
    { n: 2, label: "Services" },
    { n: 3, label: "Contact" },
    { n: 4, label: "Confirmation" },
  ];

  return (
    <div className="min-h-screen bg-bg-alt">
      {/* HERO BANNER */}
      <div className="gradient-hero text-white py-10 px-6">
        <div className="mx-auto max-w-3xl">
          <span className="inline-block rounded-md bg-primary text-white text-xs font-bold uppercase tracking-wider px-3 py-1 mb-4">
            Fastfix · Bruxelles
          </span>
          <h1 className="font-display text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
            Planifiez votre réparation
          </h1>
          <p className="mt-2 text-white/70 text-sm max-w-lg">
            Choisissez votre appareil, la réparation et envoyez votre demande.<br />
            Aucun paiement à l'avance.
          </p>
          <div className="flex flex-wrap gap-2 mt-4">
            {[
              { icon: "⭐", label: "Avis clients 4.9/5" },
              { icon: "✓", label: "6 mois de garantie" },
              { icon: "📍", label: "Bruxelles" },
            ].map((b) => (
              <span key={b.label} className="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-medium">
                <span>{b.icon}</span> {b.label}
              </span>
            ))}
          </div>
        </div>
      </div>

      {/* STEP INDICATOR */}
      <div className="mx-auto max-w-3xl">
        <div className="flex border-b border-border bg-white rounded-t-none overflow-hidden">
          {steps.map((s) => (
            <button
              key={s.n}
              onClick={() => {
                if (s.n < step || (s.n === 2 && selectedDevice) || (s.n === 3 && selectedRepairs.length > 0)) {
                  if (s.n <= step) setStep(s.n);
                }
              }}
              className={`flex-1 flex items-center justify-center gap-2 py-3.5 text-xs sm:text-sm font-semibold transition-colors ${
                s.n === step
                  ? "bg-primary text-white"
                  : s.n < step
                  ? "bg-primary/10 text-primary"
                  : "text-text-muted"
              }`}
            >
              <span className={`flex items-center justify-center h-5 w-5 rounded-full text-[10px] font-bold ${
                s.n === step ? "bg-white text-primary" : s.n < step ? "bg-primary text-white" : "bg-border text-text-muted"
              }`}>
                {s.n < step && !submitted ? "✓" : s.n}
              </span>
              <span className="hidden sm:inline">{s.label}</span>
            </button>
          ))}
        </div>
      </div>

      {/* CONTENT */}
      <div className="mx-auto max-w-3xl px-4 sm:px-6 py-8">
        <div className="bg-white rounded-2xl shadow-sm border border-border p-6 sm:p-8">

          {/* ═══ STEP 1: APPAREIL ═══ */}
          {step === 1 && (
            <div>
              <p className="text-primary text-xs font-bold tracking-wider uppercase mb-1">Étape 1 · Appareil</p>
              <h2 className="font-display text-xl sm:text-2xl font-bold mb-1">Choisissez votre appareil</h2>
              <p className="text-text-muted text-sm mb-6">Commencez par la marque, puis choisissez le type et le modèle.</p>

              {/* Brand pills */}
              <label className="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Choisissez votre marque</label>
              <div className="flex flex-wrap gap-2 mb-5">
                {brands.map((b) => (
                  <button
                    key={b.id}
                    onClick={() => handleBrandChange(b.id)}
                    className={`rounded-full px-4 py-2 text-xs font-bold tracking-wider transition-all ${
                      brand === b.id
                        ? "bg-bg-dark text-white"
                        : "bg-bg-alt text-text-muted hover:bg-border"
                    }`}
                  >
                    {b.label}
                  </button>
                ))}
              </div>

              {/* Device type pills */}
              <label className="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Choisissez le type d'appareil</label>
              <div className="flex flex-wrap gap-2 mb-5">
                {currentDeviceTypes.map((t) => (
                  <button
                    key={t.id}
                    onClick={() => handleDeviceTypeChange(t.id)}
                    className={`rounded-full px-4 py-2 text-xs font-bold tracking-wider transition-all ${
                      deviceType === t.id
                        ? "bg-bg-dark text-white"
                        : "bg-bg-alt text-text-muted hover:bg-border"
                    }`}
                  >
                    {t.label}
                  </button>
                ))}
              </div>

              {/* Search */}
              <label className="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Choisissez votre modèle</label>
              <div className="relative mb-4">
                <svg className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor"><circle cx="11" cy="11" r="8"/><path strokeLinecap="round" d="m21 21-4.35-4.35"/></svg>
                <input
                  type="text"
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  placeholder="Rechercher un modèle ou un numéro de modèle..."
                  className="w-full rounded-xl border border-border pl-10 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                />
              </div>

              <p className="text-xs text-text-muted mb-3">Vous ne connaissez pas le modèle exact ? Choisissez le plus proche ou contactez-nous.</p>

              {/* Model grid */}
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-[500px] overflow-y-auto pr-1">
                {filteredDevices.map((d) => (
                  <button
                    key={d.name}
                    onClick={() => { setSelectedDevice(d); setStep(2); setSelectedRepairs([]); }}
                    className="flex items-center gap-3 rounded-xl border border-border hover:border-primary hover:bg-primary/5 p-3 text-left transition-all group"
                  >
                    <div className="h-12 w-12 flex-shrink-0 flex items-center justify-center">
                      <img src={d.img} alt={d.name} className="max-h-full max-w-full object-contain transition-transform group-hover:scale-110" loading="lazy" />
                    </div>
                    <div className="min-w-0">
                      <span className="block font-semibold text-sm text-text truncate">{d.name}</span>
                      <span className="block text-[10px] text-text-muted truncate">Modèle n° {d.models}</span>
                    </div>
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* ═══ STEP 2: SERVICES ═══ */}
          {step === 2 && selectedDevice && (
            <div>
              <button onClick={() => setStep(1)} className="inline-flex items-center gap-1.5 rounded-full border border-border px-4 py-2 text-xs font-medium text-text-muted hover:border-primary hover:text-primary transition-all mb-4">
                ← Modifier l'appareil
              </button>
              <p className="text-primary text-xs font-bold tracking-wider uppercase mb-1">Étape 2 · Services</p>
              <div className="flex items-center gap-3 mb-1">
                <h2 className="font-display text-xl sm:text-2xl font-bold">Choisissez votre réparation</h2>
                <span className="rounded-full bg-bg-alt px-3 py-1 text-xs font-semibold text-text">{selectedDevice.name}</span>
              </div>
              <p className="text-text-muted text-sm mb-6">Sélectionnez ce qui doit être réparé. Vous voyez directement le prix et la garantie.</p>

              {/* Search repairs */}
              <div className="relative mb-6">
                <svg className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor"><circle cx="11" cy="11" r="8"/><path strokeLinecap="round" d="m21 21-4.35-4.35"/></svg>
                <input type="text" placeholder="Rechercher une réparation..." className="w-full rounded-xl border border-border pl-10 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
              </div>

              {/* Repair categories */}
              {getRepairs(deviceType).map((cat) => (
                <div key={cat.title} className="mb-8">
                  <h3 className="flex items-center gap-2 font-display font-bold text-base mb-4">
                    <span>{cat.icon}</span> {cat.title}
                  </h3>
                  <div className="grid sm:grid-cols-2 gap-4">
                    {cat.repairs.map((r) => {
                      const isSelected = selectedRepairs.some((sr) => sr.name === r.name);
                      return (
                        <div
                          key={r.name}
                          className={`rounded-xl border-2 p-5 transition-all ${
                            isSelected ? "border-primary bg-primary/5" : "border-border hover:border-primary/40"
                          }`}
                        >
                          {r.badge && (
                            <span className={`inline-block rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white mb-3 ${
                              r.badgeColor === "red" ? "bg-red-500" : "bg-primary"
                            }`}>
                              {r.badge}
                            </span>
                          )}
                          <h4 className="font-display font-bold text-sm mb-1">{r.name}</h4>
                          <p className="text-xs text-text-muted mb-3 leading-relaxed"><strong>Idéal pour :</strong> {r.desc}</p>
                          <div className="flex items-baseline gap-2 mb-3">
                            {r.oldPrice && <span className="text-xs text-text-muted line-through">€{r.oldPrice}</span>}
                            <span className="font-display text-2xl font-bold text-text">
                              {r.price === 0 ? "Gratuit*" : `€${r.price}`}
                            </span>
                          </div>
                          <ul className="space-y-1.5 mb-3">
                            {r.features.map((f) => (
                              <li key={f} className="flex items-start gap-2 text-xs text-text-light">
                                <svg className="h-3.5 w-3.5 text-primary flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                {f}
                              </li>
                            ))}
                          </ul>
                          {r.attention && (
                            <div className="rounded-lg bg-warning/10 border border-warning/20 px-3 py-2 mb-3">
                              <p className="text-[10px] font-bold text-warning uppercase tracking-wider mb-0.5">Attention</p>
                              <p className="text-xs text-text-light">{r.attention}</p>
                            </div>
                          )}
                          <div className="flex items-center gap-3 text-[10px] text-text-muted mb-3">
                            <span className="flex items-center gap-1">🕐 {r.time}</span>
                            <span className="flex items-center gap-1">🛡️ {r.warranty}</span>
                          </div>
                          <button
                            onClick={() => toggleRepair(r)}
                            className={`w-full rounded-lg border-2 py-2.5 text-xs font-bold uppercase tracking-wider transition-all ${
                              isSelected
                                ? "border-primary bg-primary text-white"
                                : "border-bg-dark text-bg-dark hover:bg-bg-dark hover:text-white"
                            }`}
                          >
                            {isSelected ? "✓ Sélectionné" : "Choisir cette réparation"}
                          </button>
                        </div>
                      );
                    })}
                  </div>
                </div>
              ))}

              {/* Continue button */}
              {selectedRepairs.length > 0 && (
                <div className="sticky bottom-0 bg-white border-t border-border -mx-6 sm:-mx-8 px-6 sm:px-8 py-4 mt-4 flex items-center justify-between">
                  <div>
                    <span className="text-xs text-text-muted">{selectedRepairs.length} réparation{selectedRepairs.length > 1 ? "s" : ""}</span>
                    <span className="block font-display text-xl font-bold text-primary">€{total}</span>
                  </div>
                  <button
                    onClick={() => setStep(3)}
                    className="rounded-full bg-primary hover:bg-primary-dark px-6 py-3 text-sm font-semibold text-white transition-colors"
                  >
                    Continuer →
                  </button>
                </div>
              )}
            </div>
          )}

          {/* ═══ STEP 3: CONTACT ═══ */}
          {step === 3 && selectedDevice && (
            <div>
              <button onClick={() => setStep(2)} className="inline-flex items-center gap-1.5 rounded-full border border-border px-4 py-2 text-xs font-medium text-text-muted hover:border-primary hover:text-primary transition-all mb-4">
                ← Modifier les services
              </button>
              <p className="text-primary text-xs font-bold tracking-wider uppercase mb-1">Étape 3 · Contact</p>
              <h2 className="font-display text-xl sm:text-2xl font-bold mb-1">Entrez vos coordonnées</h2>
              <p className="text-text-muted text-sm mb-6">Nous utilisons vos coordonnées uniquement pour confirmer ce rendez-vous.</p>

              {/* Summary */}
              <div className="rounded-xl bg-bg-alt border border-border p-5 mb-6">
                <p className="text-[10px] font-bold uppercase tracking-widest text-primary mb-3">Votre sélection</p>
                <div className="space-y-2 text-sm">
                  <div className="flex justify-between">
                    <span className="text-text-muted">Appareil</span>
                    <span className="font-medium text-text">{brands.find(b => b.id === brand)?.label} {selectedDevice.name}</span>
                  </div>
                  {selectedRepairs.map((r) => (
                    <div key={r.name} className="flex justify-between">
                      <span className="text-text-muted">Réparation</span>
                      <span className="font-medium text-text">{r.name} — €{r.price}</span>
                    </div>
                  ))}
                  <div className="border-t border-border pt-2 flex justify-between">
                    <span className="font-semibold text-text">Total estimé</span>
                    <span className="font-display text-xl font-bold text-primary">€{total}</span>
                  </div>
                </div>
                <div className="flex flex-wrap gap-2 mt-3">
                  {["6 mois de garantie", "Réparation express", "Aucun paiement à l'avance"].map((t) => (
                    <span key={t} className="rounded-full bg-primary/10 text-primary px-2.5 py-1 text-[10px] font-semibold">{t}</span>
                  ))}
                </div>
              </div>

              {/* Client type */}
              <label className="block text-[10px] font-bold uppercase tracking-widest text-text-muted mb-2">Vous réservez en tant que</label>
              <div className="grid grid-cols-2 gap-3 mb-6">
                {(["particulier", "entreprise"] as const).map((t) => (
                  <button
                    key={t}
                    onClick={() => setClientType(t)}
                    className={`rounded-xl py-3 text-sm font-bold uppercase tracking-wider transition-all ${
                      clientType === t
                        ? "bg-bg-dark text-white"
                        : "bg-bg-alt text-text-muted hover:bg-border"
                    }`}
                  >
                    {t}
                  </button>
                ))}
              </div>

              {/* Form fields */}
              <div className="grid sm:grid-cols-2 gap-4 mb-4">
                <div>
                  <label className="block text-xs font-bold text-text mb-1.5">Nom <span className="text-red-500">*</span></label>
                  <input
                    type="text"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    placeholder="Prénom et nom"
                    className="w-full rounded-xl border border-border px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                    required
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-text mb-1.5">Téléphone <span className="text-red-500">*</span></label>
                  <input
                    type="tel"
                    value={formData.phone}
                    onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    placeholder="+32 ..."
                    className="w-full rounded-xl border border-border px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                    required
                  />
                </div>
              </div>
              <div className="mb-4">
                <label className="block text-xs font-bold text-text mb-1.5">Email <span className="text-red-500">*</span></label>
                <input
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  placeholder="nom@exemple.be"
                  className="w-full rounded-xl border border-border px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                  required
                />
              </div>
              <div className="mb-6">
                <label className="block text-xs font-bold text-text mb-1.5">Remarques (optionnel)</label>
                <textarea
                  value={formData.notes}
                  onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                  rows={3}
                  placeholder="Détails sur votre appareil..."
                  className="w-full rounded-xl border border-border px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors resize-y"
                />
              </div>

              {/* Actions */}
              <div className="flex items-center justify-between">
                <button onClick={() => setStep(2)} className="inline-flex items-center gap-1.5 rounded-full border border-border px-5 py-2.5 text-xs font-semibold text-text-muted hover:border-primary hover:text-primary transition-all">
                  ← Retour
                </button>
                <button
                  onClick={handleSubmit}
                  disabled={!formData.name || !formData.phone || !formData.email}
                  className="inline-flex items-center gap-2 rounded-full bg-primary hover:bg-primary-dark disabled:opacity-40 disabled:cursor-not-allowed px-6 py-3 text-sm font-semibold text-white transition-colors"
                >
                  ✓ Confirmer la demande
                </button>
              </div>
            </div>
          )}

          {/* ═══ STEP 4: CONFIRMATION ═══ */}
          {step === 4 && submitted && (
            <div className="text-center py-8">
              <div className="flex items-center justify-center mb-6">
                <div className="h-16 w-16 rounded-full bg-primary flex items-center justify-center">
                  <svg className="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                </div>
              </div>
              <h2 className="font-display text-2xl font-bold mb-2">Demande envoyée !</h2>
              <p className="text-text-muted text-sm mb-8">Merci ! Notre équipe vous recontacte dans les 2 heures ouvrées.</p>

              <div className="inline-block text-left rounded-xl border border-border p-6 mb-8">
                <p className="font-semibold text-sm mb-2">{brands.find(b => b.id === brand)?.label} {selectedDevice?.name}</p>
                {selectedRepairs.map((r) => (
                  <p key={r.name} className="text-sm text-text-muted">• {r.name} — €{r.price}</p>
                ))}
                <p className="font-bold text-sm mt-2">Total : €{total}</p>
                <div className="border-t border-border mt-3 pt-3 text-xs text-text-muted">
                  <p>{formData.phone} · {formData.email}</p>
                </div>
              </div>

              <div>
                <a href="/" className="inline-flex items-center gap-2 rounded-full bg-primary hover:bg-primary-dark px-6 py-3 text-sm font-semibold text-white transition-colors">
                  ↩ Retour à l'accueil
                </a>
              </div>
            </div>
          )}

        </div>
      </div>
    </div>
  );
}

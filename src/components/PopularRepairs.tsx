import { useState, useEffect } from "react";

import { FASTFIX_API_URL } from "../lib/fastfix";

interface RemoteFeaturedRepair {
  name: string;
  desc: string;
  price: number;
  oldPrice?: number;
  icon: string;
  device: string;
  badge?: string;
  badgeColor?: string;
}

interface PopularRepair {
  name: string;
  device: string;
  price: string;
  icon: string;
}

// Fallback local si l'API WordPress est injoignable — garde le site fonctionnel.
const fallbackRepairs: PopularRepair[] = [
  { name: "Remplacement de la vitre", device: "iPhone", price: "119", icon: "📱" },
  { name: "Batterie", device: "iPhone", price: "129", icon: "🔋" },
  { name: "Diagnostic", device: "iPhone", price: "0", icon: "🔍" },
  { name: "Vitre arrière", device: "iPhone", price: "89", icon: "🔧" },
  { name: "Écran complet iPad", device: "iPad", price: "199", icon: "📱" },
  { name: "Traitement dégât des eaux", device: "iPhone", price: "79", icon: "💧" },
];

export default function PopularRepairs() {
  const [repairs, setRepairs] = useState<PopularRepair[]>(fallbackRepairs);

  // Récupère les réparations marquées "populaire" depuis WordPress (éditable
  // dans wp-admin → FastFix → Réparations, case "Réparation populaire").
  useEffect(() => {
    fetch(`${FASTFIX_API_URL}/repairs/featured`)
      .then((res) => (res.ok ? res.json() : Promise.reject(res)))
      .then((list: RemoteFeaturedRepair[]) => {
        if (Array.isArray(list) && list.length > 0) {
          setRepairs(
            list.map((r) => ({
              name: r.name,
              device: r.device,
              price: r.price === 0 ? "Gratuit*" : String(r.price),
              icon: r.icon || "🔧",
            }))
          );
        }
      })
      .catch(() => {});
  }, []);

  return (
    <div data-reveal-children className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
      {repairs.map((r) => (
        <a key={r.name} href="/rdv" className="card group flex flex-col items-center p-5 text-center">
          <div className="h-12 w-12 flex items-center justify-center mb-3 rounded-xl bg-primary/10 text-2xl">
            {r.icon}
          </div>
          <span className="font-display font-semibold text-sm text-text">{r.name}</span>
          <span className="text-xs text-text-muted mt-1">{r.device}</span>
          <span className="text-xs text-primary font-medium mt-1">
            {r.price === "Gratuit*" ? "Gratuit*" : `à partir de € ${r.price}`}
          </span>
        </a>
      ))}
    </div>
  );
}

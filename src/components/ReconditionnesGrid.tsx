import { useState, useEffect } from "react";

import { FASTFIX_API_URL } from "../lib/fastfix";

interface RefurbishedProduct {
  id: number;
  name: string;
  grade: string;
  price: number;
  oldPrice?: number;
  badge?: string;
  color: string;
  storage: string;
  warranty: string;
  image: string;
}

// Fallback local si l'API WordPress est injoignable — garde le site fonctionnel.
const fallbackProducts: RefurbishedProduct[] = [
  { id: 0, name: "iPhone 16 Pro Max", grade: "A+", price: 899, oldPrice: 1299, image: "/images/devices/iphone-16-pro-max.webp", badge: "Best Seller", color: "Titane Naturel", storage: "256 Go", warranty: "12 mois" },
  { id: 0, name: "iPhone 16", grade: "A", price: 629, oldPrice: 969, image: "/images/devices/iphone-16.webp", color: "Noir", storage: "128 Go", warranty: "12 mois" },
  { id: 0, name: "iPhone 17 Pro", grade: "A+", price: 979, oldPrice: 1399, image: "/images/devices/iphone-17-pro.webp", badge: "Nouveau", color: "Noir Sidéral", storage: "256 Go", warranty: "12 mois" },
  { id: 0, name: "Galaxy S26 Ultra", grade: "A", price: 849, oldPrice: 1299, image: "/images/devices/galaxy-s26-ultra.webp", color: "Noir Fantôme", storage: "256 Go", warranty: "12 mois" },
  { id: 0, name: "Galaxy S26", grade: "A+", price: 599, oldPrice: 899, image: "/images/devices/galaxy-s26.webp", badge: "Petit prix", color: "Crème", storage: "128 Go", warranty: "12 mois" },
  { id: 0, name: "Galaxy S24", grade: "B+", price: 449, oldPrice: 799, image: "/images/devices/galaxy-s24.webp", color: "Violet", storage: "128 Go", warranty: "12 mois" },
  { id: 0, name: "iPhone 17", grade: "A", price: 699, oldPrice: 1059, image: "/images/devices/iphone-17.webp", color: "Blanc", storage: "128 Go", warranty: "12 mois" },
  { id: 0, name: "Galaxy S23 Ultra", grade: "B+", price: 399, oldPrice: 749, image: "/images/devices/galaxy-s23-ultra.webp", badge: "Prix choc", color: "Vert", storage: "256 Go", warranty: "12 mois" },
];

function badgeClass(badge: string) {
  if (badge === "Best Seller") return "badge-purple";
  if (badge === "Nouveau") return "bg-blue-50 text-blue-700 border-blue-200";
  if (badge === "Prix choc") return "bg-red-50 text-red-700 border-red-200";
  return "bg-amber-50 text-amber-700 border-amber-200";
}

export default function ReconditionnesGrid() {
  const [products, setProducts] = useState<RefurbishedProduct[]>(fallbackProducts);

  // Récupère les produits reconditionnés depuis WordPress (éditables dans
  // wp-admin → FastFix → Reconditionnés). En cas d'échec, on garde le fallback local.
  useEffect(() => {
    fetch(`${FASTFIX_API_URL}/refurbished`)
      .then((res) => (res.ok ? res.json() : Promise.reject(res)))
      .then((data: RefurbishedProduct[]) => {
        if (Array.isArray(data) && data.length > 0) {
          setProducts(data.map((p) => ({ ...p, image: p.image || "/images/devices/iphone-16.webp" })));
        }
      })
      .catch(() => {});
  }, []);

  return (
    <div data-reveal-children className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
      {products.map((p) => (
        <div key={p.id || p.name} className="card group overflow-hidden relative">
          {p.badge && (
            <span className={`absolute top-3 left-3 z-10 badge text-xs ${badgeClass(p.badge)}`}>{p.badge}</span>
          )}
          <div className="aspect-square p-6 bg-bg-alt flex items-center justify-center">
            <img src={p.image} alt={p.name} className="h-44 w-auto object-contain transition-transform duration-300 group-hover:scale-105" loading="lazy" />
          </div>
          <div className="p-5">
            <div className="flex items-center justify-between mb-1">
              <h3 className="font-display font-bold text-sm">{p.name}</h3>
              <span className="flex h-6 w-8 items-center justify-center rounded bg-primary/10 font-display font-bold text-primary text-xs">{p.grade}</span>
            </div>
            <div className="text-xs text-text-muted mb-3">
              {p.color} · {p.storage}
            </div>
            <div className="flex items-baseline gap-2 mb-3">
              <span className="font-display text-xl font-bold text-primary">{p.price} €</span>
              {p.oldPrice && (
                <>
                  <span className="text-sm text-text-muted line-through">{p.oldPrice} €</span>
                  <span className="text-xs font-semibold text-accent">-{Math.round((1 - p.price / p.oldPrice) * 100)}%</span>
                </>
              )}
            </div>
            <div className="flex items-center gap-1.5 text-xs text-text-muted mb-4">
              <svg className="h-3.5 w-3.5 text-accent" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
              Garantie {p.warranty}
            </div>
            <a href="/contact" className="block text-center rounded-full bg-primary hover:bg-primary-dark px-4 py-2.5 text-xs font-semibold text-white transition-colors">
              Réserver
            </a>
          </div>
        </div>
      ))}
    </div>
  );
}

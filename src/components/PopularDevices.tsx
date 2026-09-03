import { useState, useEffect } from "react";

import { FASTFIX_API_URL } from "../lib/fastfix";

interface RemoteDevice {
  id: number;
  name: string;
  image: string;
  featured: boolean;
  startingPrice: number | null;
  deviceType: string;
}

// Repli par famille : un appareil sans photo dans WordPress garde une image
// cohérente avec ce qu'il est, plutôt qu'une image générique unique.
const TYPE_IMAGES: Record<string, string> = {
  iphone: "/images/devices/iphone-16.webp",
  ipad: "/images/devices/ipad-pro.webp",
  "apple-watch": "/images/devices/apple-watch.webp",
  macbook: "/images/devices/macbook-air.webp",
  "galaxy-s": "/images/devices/galaxy-s26-ultra.webp",
  "galaxy-a": "/images/devices/galaxy-s24.webp",
  "galaxy-tab": "/images/devices/galaxy-tab.webp",
  "galaxy-z": "/images/devices/galaxy-s24.webp",
  pixel: "/images/devices/pixel-10-pro.webp",
  "pixel-tablet": "/images/devices/pixel-10-pro.webp",
};

interface PopularDevice {
  name: string;
  img: string;
  price: string;
}

// Fallback local si l'API WordPress est injoignable — garde le site fonctionnel.
const fallbackDevices: PopularDevice[] = [
  { name: "iPhone 16", img: "/images/devices/iphone-16.webp", price: "79" },
  { name: "iPhone 17 Pro", img: "/images/devices/iphone-17-pro.webp", price: "89" },
  { name: "iPhone 16 Pro Max", img: "/images/devices/iphone-16-pro-max.webp", price: "89" },
  { name: "iPhone 17", img: "/images/devices/iphone-17.webp", price: "79" },
  { name: "Galaxy S26 Ultra", img: "/images/devices/galaxy-s26-ultra.webp", price: "89" },
  { name: "Galaxy S26", img: "/images/devices/galaxy-s26.webp", price: "79" },
  { name: "Galaxy S24", img: "/images/devices/galaxy-s24.webp", price: "69" },
  { name: "iPad Pro", img: "/images/devices/ipad-pro.webp", price: "99" },
  { name: "MacBook Air", img: "/images/devices/macbook-air.webp", price: "129" },
];

export default function PopularDevices() {
  const [devices, setDevices] = useState<PopularDevice[]>(fallbackDevices);

  // Récupère les appareils marqués "populaire" depuis WordPress (éditable
  // dans wp-admin → FastFix → Appareils, case "Appareil populaire").
  useEffect(() => {
    fetch(`${FASTFIX_API_URL}/devices`)
      .then((res) => (res.ok ? res.json() : Promise.reject(res)))
      .then((list: RemoteDevice[]) => {
        const featured = Array.isArray(list) ? list.filter((d) => d.featured) : [];
        if (featured.length > 0) {
          setDevices(
            featured.map((d) => ({
              name: d.name,
              // 1) photo WordPress, 2) image d'origine du même modèle,
              // 3) image représentative de la famille
              img:
                d.image ||
                fallbackDevices.find((f) => f.name === d.name)?.img ||
                TYPE_IMAGES[d.deviceType] ||
                "/images/devices/iphone-16.webp",
              price: d.startingPrice != null ? String(d.startingPrice) : "—",
            }))
          );
        }
      })
      .catch(() => {});
  }, []);

  return (
    <div data-reveal-children className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
      {devices.map((d) => (
        <a key={d.name} href="/prix" className="card group flex flex-col items-center p-5 text-center">
          <div className="h-28 flex items-center justify-center mb-3">
            <img src={d.img} alt={d.name} className="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-110" loading="lazy" />
          </div>
          <span className="font-display font-semibold text-sm text-text">{d.name}</span>
          <span className="text-xs text-primary font-medium mt-1">à partir de € {d.price}</span>
        </a>
      ))}
    </div>
  );
}

import { useState, useEffect } from "react";

/**
 * Point d'entrée unique vers le backend WordPress (plugin "FastFix — Hascom").
 * À remplacer par https://fastfix.be/wp-json/fastfix/v1 une fois le domaine
 * final branché sur WordPress.
 */
export const FASTFIX_API_URL = "https://ahmedc12.sg-host.com/wp-json/fastfix/v1";

export interface SiteConfig {
  business: { name: string; tagline: string; legalName: string; intro: string };
  contact: { phone: string; phoneLink: string; whatsapp: string; email: string };
  address: { street: string; postalCode: string; city: string; full: string; mapsUrl: string };
  hours: {
    days: Record<string, { closed: boolean; open: string; close: string }>;
    summary: string;
    isOpen: boolean;
    status: string;
  };
  socials: Partial<Record<"facebook" | "instagram" | "linkedin" | "tiktok", string>>;
  stats: { googleRating: string; googleReviews: string; repairsCount: string; sinceYear: string };
  promises: string[];
  reviews: { name: string; text: string; stars: number; device: string; date: string }[];
  faq: { q: string; a: string }[];
}

/**
 * Charge les réglages du site depuis WordPress. Renvoie `null` tant que la
 * requête n'a pas abouti — les composants affichent alors leurs valeurs par
 * défaut, si bien que le site reste correct même si l'API est injoignable.
 *
 * La réponse est mise en cache pour la durée de la visite : un seul appel
 * réseau, quel que soit le nombre de composants qui l'utilisent.
 */
let configCache: SiteConfig | null = null;
let configPromise: Promise<SiteConfig> | null = null;

export function fetchSiteConfig(): Promise<SiteConfig> {
  if (configCache) return Promise.resolve(configCache);
  if (!configPromise) {
    configPromise = fetch(`${FASTFIX_API_URL}/config`)
      .then((res) => (res.ok ? res.json() : Promise.reject(res)))
      .then((data: SiteConfig) => {
        configCache = data;
        return data;
      });
  }
  return configPromise;
}

export function useSiteConfig(): SiteConfig | null {
  const [config, setConfig] = useState<SiteConfig | null>(configCache);

  useEffect(() => {
    let cancelled = false;
    fetchSiteConfig()
      .then((data) => {
        if (!cancelled) setConfig(data);
      })
      .catch(() => {});
    return () => {
      cancelled = true;
    };
  }, []);

  return config;
}

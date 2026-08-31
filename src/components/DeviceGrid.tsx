const devices = [
  { name: "iPhone 17 Pro Max", price: 149, img: "/images/devices/iphone-17-pro-max.webp" },
  { name: "iPhone 17 Pro", price: 129, img: "/images/devices/iphone-17-pro.webp" },
  { name: "iPhone 17", price: 109, img: "/images/devices/iphone-17.webp" },
  { name: "iPhone 16 Pro Max", price: 119, img: "/images/devices/iphone-16-pro-max.webp" },
  { name: "iPhone 16", price: 89, img: "/images/devices/iphone-16.webp" },
  { name: "Galaxy S26 Ultra", price: 129, img: "/images/devices/galaxy-s26-ultra.webp" },
  { name: "Galaxy S24", price: 99, img: "/images/devices/galaxy-s24.webp" },
  { name: "iPad Pro", price: 149, img: "/images/devices/ipad-pro.webp" },
  { name: "MacBook Air", price: 199, img: "/images/devices/macbook-air.webp" },
  { name: "Apple Watch", price: 89, img: "/images/devices/apple-watch.webp" },
  { name: "Galaxy Tab", price: 119, img: "/images/devices/galaxy-tab.webp" },
  { name: "PS5", price: 99, img: "/images/devices/ps5.webp" },
];

export default function DeviceGrid() {
  return (
    <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      {devices.map((device) => (
        <a
          key={device.name}
          href={`/reparations`}
          className="card group relative flex flex-col items-center gap-3 p-5 text-center"
        >
          <div className="relative w-full aspect-[3/4] flex items-center justify-center overflow-hidden rounded-xl bg-bg-alt p-3">
            <img
              src={device.img}
              alt={device.name}
              className="max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-110"
              loading="lazy"
            />
          </div>
          <span className="font-display font-semibold text-sm text-text">
            {device.name}
          </span>
          <span className="text-xs text-text-muted">
            à partir de{" "}
            <span className="font-display font-bold text-primary text-base">
              {device.price} €
            </span>
          </span>
          <div className="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
            <svg className="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </div>
        </a>
      ))}
    </div>
  );
}

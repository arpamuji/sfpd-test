"use client";

import React, { useEffect, useRef } from "react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

// Fix Leaflet marker icon issue with default markers
const markerIcon = L.divIcon({
    className: "text-primary",
    html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-primary drop-shadow-md">
        <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
    </svg>`,
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32],
});

interface Props {
    latitude: number;
    longitude: number;
    onLocationChange?: (lat: number, lng: number) => void;
    className?: string;
}

export default function LocationMap({
    latitude,
    longitude,
    onLocationChange,
    className,
}: Props) {
    const mapRef = useRef<HTMLDivElement>(null);
    const mapInstance = useRef<L.Map | null>(null);
    const markerRef = useRef<L.Marker | null>(null);
    const isDragging = useRef(false);
    const prevCoords = useRef<{ lat: number; lng: number } | null>(null);

    // Initialize map once
    useEffect(() => {
        if (!mapRef.current) return;

        mapInstance.current = L.map(mapRef.current, {
            scrollWheelZoom: false,
        }).setView([latitude, longitude], 13);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(mapInstance.current);

        markerRef.current = L.marker([latitude, longitude], {
            icon: markerIcon,
            draggable: !!onLocationChange,
        }).addTo(mapInstance.current);

        // Store initial coords
        prevCoords.current = { lat: latitude, lng: longitude };

        if (onLocationChange) {
            markerRef.current.on("dragstart", () => {
                isDragging.current = true;
            });
            markerRef.current.on("dragend", function (event) {
                const marker = event.target;
                const position = marker.getLatLng();
                isDragging.current = false;
                onLocationChange(position.lat, position.lng);
                prevCoords.current = { lat: position.lat, lng: position.lng };
            });
        }

        return () => {
            if (mapInstance.current) {
                mapInstance.current.remove();
                mapInstance.current = null;
            }
        };
    }, []);

    // Update marker when props change (and not from internal drag)
    useEffect(() => {
        if (!mapInstance.current || !markerRef.current) return;
        if (isDragging.current) return;

        console.log("LocationMap: coords changed", {
            latitude,
            longitude,
            prevCoords: prevCoords.current,
        });

        // Skip if coords haven't changed
        if (
            prevCoords.current &&
            Math.abs(prevCoords.current.lat - latitude) < 0.000001 &&
            Math.abs(prevCoords.current.lng - longitude) < 0.000001
        ) {
            console.log("LocationMap: skipping update, coords same");
            return;
        }

        console.log("LocationMap: updating map view");
        // Update map and marker
        mapInstance.current.setView([latitude, longitude], 13);
        markerRef.current.setLatLng([latitude, longitude]);
        prevCoords.current = { lat: latitude, lng: longitude };
    }, [latitude, longitude]);

    return (
        <div className={className}>
            <label className="block text-sm font-medium text-foreground mb-2">
                Location
            </label>
            <div
                ref={mapRef}
                className="h-64 w-full rounded-md border border-border"
            />
        </div>
    );
}

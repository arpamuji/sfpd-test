# Phase 6: Frontend - Detailed Specification

**Tasks:** 17-19  
**Branch:** `feature/frontend` → `development`

---

## Task 17: Layout & UI Components

**Branch:** `feature/frontend` (from `development`)

**Files:**
- Create: `resources/js/Components/Layouts/AppLayout.tsx`
- Create: `resources/js/Components/UI/StatusBadge.tsx`
- Create: `resources/js/Components/UI/FileUpload.tsx`
- Create: `resources/js/Components/Maps/LocationMap.tsx`

### Steps

- [ ] **Step 1: Create AppLayout**

```tsx
import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

interface Props {
  title: string;
  children: React.ReactNode;
}

export default function AppLayout({ title, children }: Props) {
  const { post } = useForm({});

  const handleLogout = () => {
    post(route('logout'));
  };

  return (
    <>
      <Head title={title} />
      <div className="min-h-screen bg-gray-100">
        <nav className="bg-white shadow">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex justify-between h-16">
              <div className="flex">
                <Link href={route('dashboard')} className="flex items-center">
                  <span className="text-xl font-bold text-gray-800">Warehouse Approval</span>
                </Link>
                <div className="hidden sm:ml-6 sm:flex sm:space-x-8">
                  <Link
                    href={route('dashboard')}
                    className="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                  >
                    Dashboard
                  </Link>
                  <Link
                    href={route('submissions.index')}
                    className="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                  >
                    Submissions
                  </Link>
                  <Link
                    href={route('submissions.create')}
                    className="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                  >
                    New Submission
                  </Link>
                </div>
              </div>
              <div className="flex items-center">
                <button
                  onClick={handleLogout}
                  className="text-gray-500 hover:text-gray-700 text-sm font-medium"
                >
                  Logout
                </button>
              </div>
            </div>
          </div>
        </nav>
        <main className="py-10">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {children}
          </div>
        </main>
      </div>
    </>
  );
}
```

- [ ] **Step 2: Create StatusBadge**

```tsx
import React from 'react';

interface Props {
  status: string;
}

export default function StatusBadge({ status }: Props) {
  const getStatusStyles = () => {
    switch (status) {
      case 'draft':
        return 'bg-gray-100 text-gray-800';
      case 'pending_spv':
      case 'pending_kepala':
      case 'pending_manager_ops':
      case 'pending_direktur_ops':
      case 'pending_direktur_keuangan':
        return 'bg-yellow-100 text-yellow-800';
      case 'approved':
        return 'bg-green-100 text-green-800';
      case 'rejected':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusLabel = () => {
    switch (status) {
      case 'draft':
        return 'Draft';
      case 'pending_spv':
        return 'Pending SPV';
      case 'pending_kepala':
        return 'Pending Kepala';
      case 'pending_manager_ops':
        return 'Pending Manager';
      case 'pending_direktur_ops':
        return 'Pending Direktur Ops';
      case 'pending_direktur_keuangan':
        return 'Pending Direktur Keuangan';
      case 'approved':
        return 'Approved';
      case 'rejected':
        return 'Rejected';
      default:
        return status;
    }
  };

  return (
    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusStyles()}`}>
      {getStatusLabel()}
    </span>
  );
}
```

- [ ] **Step 3: Create FileUpload**

```tsx
import React, { useRef } from 'react';

interface Props {
  onFilesChange: (files: File[]) => void;
  error?: string;
}

export default function FileUpload({ onFilesChange, error }: Props) {
  const inputRef = useRef<HTMLInputElement>(null);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    onFilesChange(files);
  };

  return (
    <div>
      <label className="block text-sm font-medium text-gray-700">Documents</label>
      <p className="text-sm text-gray-500 mb-2">Minimum 3 PDF files required. Images (JPG, PNG) optional.</p>
      <div
        onClick={() => inputRef.current?.click()}
        className="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 cursor-pointer"
      >
        <div className="space-y-1 text-center">
          <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
          <div className="flex text-sm text-gray-600">
            <span className="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500">
              Upload files
            </span>
          </div>
          <p className="text-xs text-gray-500">PDF, JPG, PNG up to 5MB each</p>
        </div>
      </div>
      <input
        ref={inputRef}
        type="file"
        multiple
        accept=".pdf,.jpg,.jpeg,.png"
        onChange={handleFileChange}
        className="hidden"
      />
      {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
    </div>
  );
}
```

- [ ] **Step 4: Create LocationMap**

```tsx
import React, { useEffect, useRef } from 'react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

interface Props {
  latitude: number;
  longitude: number;
  onLocationChange?: (lat: number, lng: number) => void;
}

export default function LocationMap({ latitude, longitude, onLocationChange }: Props) {
  const mapRef = useRef<HTMLDivElement>(null);
  const mapInstance = useRef<L.Map | null>(null);
  const markerRef = useRef<L.Marker | null>(null);

  useEffect(() => {
    if (mapRef.current && !mapInstance.current) {
      mapInstance.current = L.map(mapRef.current).setView([latitude, longitude], 13);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
      }).addTo(mapInstance.current);

      markerRef.current = L.marker([latitude, longitude]).addTo(mapInstance.current);
    }

    return () => {
      if (mapInstance.current) {
        mapInstance.current.remove();
        mapInstance.current = null;
      }
    };
  }, []);

  useEffect(() => {
    if (mapInstance.current && markerRef.current) {
      mapInstance.current.setView([latitude, longitude], 13);
      markerRef.current.setLatLng([latitude, longitude]);
    }
  }, [latitude, longitude]);

  return (
    <div>
      <label className="block text-sm font-medium text-gray-700 mb-2">Location</label>
      <div ref={mapRef} className="h-64 w-full rounded-md border border-gray-300" />
    </div>
  );
}
```

---

## Task 18: Auth Pages

**Files:**
- Create: `resources/js/Pages/Auth/Login.tsx`
- Create: `resources/js/Pages/Auth/TwoFactorVerify.tsx`

### Steps

- [ ] **Step 1: Create Login page**

```tsx
import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';

export default function Login() {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    remember: false,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('login'));
  };

  return (
    <>
      <Head title="Login" />
      <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-md w-full space-y-8">
          <div>
            <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
              Warehouse Approval System
            </h2>
            <p className="mt-2 text-center text-sm text-gray-600">Sign in to your account</p>
          </div>
          <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
            <div className="rounded-md shadow-sm -space-y-px">
              <div>
                <label htmlFor="email" className="sr-only">Email</label>
                <input
                  id="email"
                  name="email"
                  type="email"
                  required
                  className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                  placeholder="Email address"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                />
                {errors.email && <p className="text-red-500 text-xs mt-1">{errors.email}</p>}
              </div>
              <div>
                <label htmlFor="password" className="sr-only">Password</label>
                <input
                  id="password"
                  name="password"
                  type="password"
                  required
                  className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                  placeholder="Password"
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                />
                {errors.password && <p className="text-red-500 text-xs mt-1">{errors.password}</p>}
              </div>
            </div>

            <div>
              <button
                type="submit"
                disabled={processing}
                className="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
              >
                Sign in
              </button>
            </div>
          </form>
        </div>
      </div>
    </>
  );
}
```

- [ ] **Step 2: Create TwoFactorVerify page**

```tsx
import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';

export default function TwoFactorVerify() {
  const { data, setData, post, processing, errors } = useForm({
    code: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('2fa.verify'));
  };

  return (
    <>
      <Head title="2FA Verification" />
      <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-md w-full space-y-8">
          <div>
            <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
              Two-Factor Authentication
            </h2>
            <p className="mt-2 text-center text-sm text-gray-600">
              Enter the code from your authenticator app
            </p>
          </div>
          <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
            <div>
              <label htmlFor="code" className="sr-only">Authentication Code</label>
              <input
                id="code"
                name="code"
                type="text"
                maxLength={6}
                required
                className="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center text-2xl tracking-widest"
                placeholder="000000"
                value={data.code}
                onChange={(e) => setData('code', e.target.value.replace(/\D/g, ''))}
              />
              {errors.code && <p className="text-red-500 text-xs mt-1">{errors.code}</p>}
            </div>

            <div>
              <button
                type="submit"
                disabled={processing}
                className="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
              >
                Verify
              </button>
            </div>
          </form>
        </div>
      </div>
    </>
  );
}
```

---

## Task 19: Submission Pages

**Files:**
- Create: `resources/js/Pages/Dashboard/Dashboard.tsx`
- Create: `resources/js/Pages/Submissions/Index.tsx`
- Create: `resources/js/Pages/Submissions/Create.tsx`
- Create: `resources/js/Pages/Submissions/Show.tsx`

### Steps

- [ ] **Step 1: Create Dashboard page**

```tsx
import React from 'react';
import AppLayout from '@/Components/Layouts/AppLayout';
import StatusBadge from '@/Components/UI/StatusBadge';
import { Link } from '@inertiajs/react';

interface Submission {
  id: string;
  warehouse_name: string;
  status: string;
  budget_estimate: number;
  created_at: string;
}

interface Props {
  mySubmissions: { data: Submission[] };
  pendingApprovals: Submission[];
}

export default function Dashboard({ mySubmissions, pendingApprovals }: Props) {
  return (
    <AppLayout title="Dashboard">
      <h1 className="text-2xl font-bold text-gray-900 mb-8">Dashboard</h1>

      {pendingApprovals.length > 0 && (
        <div className="mb-8">
          <h2 className="text-xl font-semibold text-gray-800 mb-4">Pending Approvals</h2>
          <div className="bg-white shadow overflow-hidden sm:rounded-lg">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {pendingApprovals.map((submission) => (
                  <tr key={submission.id}>
                    <td className="px-6 py-4 text-sm text-gray-900">{submission.warehouse_name}</td>
                    <td className="px-6 py-4 text-sm text-gray-500">
                      Rp {parseInt(submission.budget_estimate).toLocaleString()}
                    </td>
                    <td className="px-6 py-4 text-sm">
                      <StatusBadge status={submission.status} />
                    </td>
                    <td className="px-6 py-4 text-sm">
                      <Link
                        href={route('submissions.show', submission.id)}
                        className="text-indigo-600 hover:text-indigo-900"
                      >
                        Review
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      <div>
        <h2 className="text-xl font-semibold text-gray-800 mb-4">My Submissions</h2>
        <div className="bg-white shadow overflow-hidden sm:rounded-lg">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {mySubmissions.data.map((submission) => (
                <tr key={submission.id}>
                  <td className="px-6 py-4 text-sm text-gray-900">{submission.warehouse_name}</td>
                  <td className="px-6 py-4 text-sm text-gray-500">
                    Rp {parseInt(submission.budget_estimate).toLocaleString()}
                  </td>
                  <td className="px-6 py-4 text-sm">
                    <StatusBadge status={submission.status} />
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-500">
                    {new Date(submission.created_at).toLocaleDateString()}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </AppLayout>
  );
}
```

- [ ] **Step 2: Create Submissions Index page**

```tsx
import React from 'react';
import AppLayout from '@/Components/Layouts/AppLayout';
import StatusBadge from '@/Components/UI/StatusBadge';
import { Link } from '@inertiajs/react';

interface Submission {
  id: string;
  warehouse_name: string;
  status: string;
  budget_estimate: number;
  created_at: string;
}

interface Props {
  submissions: { data: Submission[] };
}

export default function Index({ submissions }: Props) {
  return (
    <AppLayout title="My Submissions">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">My Submissions</h1>
        <Link
          href={route('submissions.create')}
          className="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
        >
          New Submission
        </Link>
      </div>

      <div className="bg-white shadow overflow-hidden sm:rounded-lg">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {submissions.data.map((submission) => (
              <tr key={submission.id}>
                <td className="px-6 py-4 text-sm text-gray-900">{submission.warehouse_name}</td>
                <td className="px-6 py-4 text-sm text-gray-500">
                  Rp {parseInt(submission.budget_estimate).toLocaleString()}
                </td>
                <td className="px-6 py-4 text-sm">
                  <StatusBadge status={submission.status} />
                </td>
                <td className="px-6 py-4 text-sm text-gray-500">
                  {new Date(submission.created_at).toLocaleDateString()}
                </td>
                <td className="px-6 py-4 text-sm">
                  <Link
                    href={route('submissions.show', submission.id)}
                    className="text-indigo-600 hover:text-indigo-900"
                  >
                    View
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </AppLayout>
  );
}
```

- [ ] **Step 3: Create Submission page**

```tsx
import React, { useState } from 'react';
import AppLayout from '@/Components/Layouts/AppLayout';
import { useForm, Head } from '@inertiajs/react';

export default function Create() {
  const [files, setFiles] = useState<File[]>([]);
  
  const { data, setData, post, processing, errors } = useForm({
    warehouse_name: '',
    warehouse_address: '',
    latitude: '',
    longitude: '',
    budget_estimate: '',
    description: '',
    files: [] as File[],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('warehouse_name', data.warehouse_name);
    formData.append('warehouse_address', data.warehouse_address);
    formData.append('latitude', data.latitude);
    formData.append('longitude', data.longitude);
    formData.append('budget_estimate', data.budget_estimate);
    if (data.description) formData.append('description', data.description);
    
    files.forEach((file) => {
      formData.append('files[]', file);
    });

    post(route('submissions.store'), {
      body: formData,
      forceFormData: true,
    });
  };

  return (
    <AppLayout title="New Submission">
      <Head title="New Submission" />
      <h1 className="text-2xl font-bold text-gray-900 mb-6">New Submission</h1>

      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="bg-white shadow sm:rounded-lg">
          <div className="px-4 py-5 sm:p-6 space-y-6">
            <div>
              <label className="block text-sm font-medium text-gray-700">Warehouse Name</label>
              <input
                type="text"
                value={data.warehouse_name}
                onChange={(e) => setData('warehouse_name', e.target.value)}
                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
              {errors.warehouse_name && <p className="text-red-500 text-xs mt-1">{errors.warehouse_name}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700">Address</label>
              <textarea
                value={data.warehouse_address}
                onChange={(e) => setData('warehouse_address', e.target.value)}
                rows={3}
                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
              {errors.warehouse_address && <p className="text-red-500 text-xs mt-1">{errors.warehouse_address}</p>}
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700">Latitude</label>
                <input
                  type="number"
                  step="0.00000001"
                  value={data.latitude}
                  onChange={(e) => setData('latitude', e.target.value)}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
                {errors.latitude && <p className="text-red-500 text-xs mt-1">{errors.latitude}</p>}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Longitude</label>
                <input
                  type="number"
                  step="0.00000001"
                  value={data.longitude}
                  onChange={(e) => setData('longitude', e.target.value)}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
                {errors.longitude && <p className="text-red-500 text-xs mt-1">{errors.longitude}</p>}
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700">Budget Estimate (Rp)</label>
              <input
                type="number"
                value={data.budget_estimate}
                onChange={(e) => setData('budget_estimate', e.target.value)}
                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
              {errors.budget_estimate && <p className="text-red-500 text-xs mt-1">{errors.budget_estimate}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700">Description</label>
              <textarea
                value={data.description}
                onChange={(e) => setData('description', e.target.value)}
                rows={4}
                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
              {errors.description && <p className="text-red-500 text-xs mt-1">{errors.description}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700">Files</label>
              <p className="text-sm text-gray-500 mb-2">Minimum 3 PDF files required. Images (JPG, PNG) optional.</p>
              <input
                type="file"
                multiple
                accept=".pdf,.jpg,.jpeg,.png"
                onChange={(e) => setFiles(Array.from(e.target.files || []))}
                className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
              />
              {errors.files && <p className="text-red-500 text-xs mt-1">{errors.files}</p>}
            </div>
          </div>
          <div className="px-4 py-3 bg-gray-50 text-right sm:px-6">
            <button
              type="submit"
              disabled={processing}
              className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
            >
              Create Submission
            </button>
          </div>
        </div>
      </form>
    </AppLayout>
  );
}
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/
git commit -m "feat(ui): add React + Inertia frontend components and pages"
```

---

## Definition of Done

Verify all items below before marking this phase complete.

### Task 17 Verification (Layout & UI Components)

- [ ] `resources/js/Components/Layouts/AppLayout.tsx` exists
- [ ] `AppLayout` renders navigation with links to Dashboard, Submissions, New Submission
- [ ] `AppLayout` has logout button that calls `post(route('logout'))`
- [ ] `AppLayout` accepts `title` prop and uses `<Head>` component
- [ ] `resources/js/Components/UI/StatusBadge.tsx` exists
- [ ] `StatusBadge` handles all statuses: draft, pending_spv, pending_kepala, pending_manager_ops, pending_direktur_ops, pending_direktur_keuangan, approved, rejected
- [ ] `StatusBadge` returns correct color classes for each status
- [ ] `resources/js/Components/UI/FileUpload.tsx` exists
- [ ] `FileUpload` has `onFilesChange` callback prop
- [ ] `FileUpload` accepts multiple files with `.pdf,.jpg,.jpeg,.png` types
- [ ] `resources/js/Components/Maps/LocationMap.tsx` exists
- [ ] `LocationMap` uses Leaflet library with OpenStreetMap tiles
- [ ] `LocationMap` accepts `latitude`, `longitude` props and displays marker

```bash
# Verification commands
test -f resources/js/Components/Layouts/AppLayout.tsx && echo "✓ AppLayout exists"
test -f resources/js/Components/UI/StatusBadge.tsx && echo "✓ StatusBadge exists"
test -f resources/js/Components/UI/FileUpload.tsx && echo "✓ FileUpload exists"
test -f resources/js/Components/Maps/LocationMap.tsx && echo "✓ LocationMap exists"
grep -q "route('logout')" resources/js/Components/Layouts/AppLayout.tsx && echo "✓ AppLayout has logout"
grep -q "pending_spv" resources/js/Components/UI/StatusBadge.tsx && echo "✓ StatusBadge handles pending statuses"
grep -q "leaflet" resources/js/Components/Maps/LocationMap.tsx && echo "✓ LocationMap uses Leaflet"
```

### Task 18 Verification (Auth Pages)

- [ ] `resources/js/Pages/Auth/Login.tsx` exists
- [ ] `Login` page has email and password inputs
- [ ] `Login` page uses `useForm` hook with `email`, `password`, `remember`
- [ ] `Login` page displays validation errors
- [ ] `Login` page submits to `route('login')`
- [ ] `resources/js/Pages/Auth/TwoFactorVerify.tsx` exists
- [ ] `TwoFactorVerify` page has 6-digit code input
- [ ] `TwoFactorVerify` page uses `useForm` hook with `code`
- [ ] `TwoFactorVerify` page restricts input to numeric only
- [ ] `TwoFactorVerify` page submits to `route('2fa.verify')`

```bash
# Verification commands
test -f resources/js/Pages/Auth/Login.tsx && echo "✓ Login page exists"
test -f resources/js/Pages/Auth/TwoFactorVerify.tsx && echo "✓ TwoFactorVerify page exists"
grep -q "useForm" resources/js/Pages/Auth/Login.tsx && echo "✓ Login uses useForm"
grep -q "digits:6" resources/js/Pages/Auth/TwoFactorVerify.tsx && echo "✓ TwoFactorVerify has 6 digit input"
grep -q "route('login')" resources/js/Pages/Auth/Login.tsx && echo "✓ Login posts to login route"
```

### Task 19 Verification (Submission Pages)

- [ ] `resources/js/Pages/Dashboard/Dashboard.tsx` exists
- [ ] `Dashboard` page shows "Pending Approvals" section (conditionally)
- [ ] `Dashboard` page shows "My Submissions" table
- [ ] `Dashboard` uses `AppLayout` and `StatusBadge`
- [ ] `resources/js/Pages/Submissions/Index.tsx` exists
- [ ] `Index` page lists submissions with "New Submission" button
- [ ] `Index` page shows warehouse name, budget, status, created date
- [ ] `resources/js/Pages/Submissions/Create.tsx` exists
- [ ] `Create` page has form with all fields: warehouse_name, warehouse_address, latitude, longitude, budget_estimate, description, files
- [ ] `Create` page handles file upload with FormData
- [ ] `Create` page submits to `route('submissions.store')`
- [ ] `resources/js/Pages/Submissions/Show.tsx` exists (or is created)
- [ ] `Show` page displays submission details and approval history

```bash
# Verification commands
test -f resources/js/Pages/Dashboard/Dashboard.tsx && echo "✓ Dashboard page exists"
test -f resources/js/Pages/Submissions/Index.tsx && echo "✓ Submissions Index exists"
test -f resources/js/Pages/Submissions/Create.tsx && echo "✓ Submissions Create exists"
grep -q "AppLayout" resources/js/Pages/Dashboard/Dashboard.tsx && echo "✓ Dashboard uses AppLayout"
grep -q "warehouse_name" resources/js/Pages/Submissions/Create.tsx && echo "✓ Create page has warehouse field"
grep -q "FormData" resources/js/Pages/Submissions/Create.tsx && echo "✓ Create page handles file upload"
```

### Build Verification

- [ ] `bun run build` completes without errors
- [ ] TypeScript compiles without type errors
- [ ] All imports resolve correctly (no missing module errors)

```bash
# Verification commands
cd resources/js && npx tsc --noEmit 2>&1 | grep -q "error" || echo "✓ TypeScript compiles without errors"
bun run build 2>&1 | grep -q "built in" && echo "✓ Vite build succeeds"
```

### Branch State Verification

- [ ] Feature branch `feature/frontend` merged to `development`
- [ ] Current branch is `development`
- [ ] Commit exists with message containing `feat(ui):` or `feat(frontend):`
- [ ] No uncommitted changes

```bash
# Verification commands
git branch --show-current | grep -q "development" && echo "✓ on development branch"
git log --oneline --grep="feat(ui)" | head -1 && echo "✓ frontend committed"
git status --porcelain | wc -l | grep -q "^0$" && echo "✓ working tree clean"
```

---

**Next Phase:** [phase-7-docs.md](phase-7-docs.md)

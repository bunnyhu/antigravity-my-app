import React from 'react';
import { useAuth } from '../context/AuthProvider';
import { Link } from 'react-router-dom';

const Dashboard = () => {
    const { user, logout } = useAuth();

    return (
        <div className="container animate-fade-in">
            <header style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '3rem' }}>
                <h1 className="page-title" style={{ margin: 0, fontSize: '2rem' }}>Dashboard</h1>
                <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
                    <span style={{ color: 'var(--text-dim)' }}>{user?.email} ({user?.role})</span>
                    <button onClick={logout} className="btn btn-secondary" style={{ padding: '0.5rem 1rem', fontSize: '0.875rem' }}>
                        Logout
                    </button>
                </div>
            </header>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '2rem' }}>
                {/* Common User Area */}
                <div className="card">
                    <h2 style={{ marginTop: 0 }}>My Profile</h2>
                    <p style={{ color: 'var(--text-dim)' }}>Welcome to your personal dashboard. Here you can manage your settings.</p>
                    <div style={{ marginTop: '1rem', padding: '1rem', background: 'rgba(255,255,255,0.05)', borderRadius: '0.5rem' }}>
                        <strong>Email:</strong> {user?.email}<br />
                        <strong>Role:</strong> <span style={{ textTransform: 'capitalize', color: 'var(--primary-color)' }}>{user?.role}</span>
                    </div>
                </div>

                {/* Manager Area */}
                {(user?.role === 'manager' || user?.role === 'admin') && (
                    <div className="card" style={{ borderColor: 'var(--secondary-color)' }}>
                        <h2 style={{ marginTop: 0, color: 'var(--secondary-color)' }}>Manager Zone</h2>
                        <p style={{ color: 'var(--text-dim)' }}>Access reports and team overview.</p>
                        <button className="btn btn-secondary" style={{ width: '100%', marginTop: '1rem' }}>View Reports</button>
                    </div>
                )}

                {/* Admin Area */}
                {user?.role === 'admin' && (
                    <div className="card" style={{ borderColor: 'var(--primary-color)' }}>
                        <h2 style={{ marginTop: 0, color: 'var(--primary-color)' }}>Admin Control</h2>
                        <p style={{ color: 'var(--text-dim)' }}>System configuration and user management.</p>
                        <Link to="/admin/users" className="btn btn-primary" style={{ width: '100%', marginTop: '1rem', textDecoration: 'none' }}>
                            Manage Users
                        </Link>
                    </div>
                )}
            </div>
        </div>
    );
};

export default Dashboard;

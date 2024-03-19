const BASE_URL = "http://localhost/api";

export const loginUser = async (username: string, password: string) => {
  const response = await fetch(`${BASE_URL}/auth/login`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify({ username, password }),
  });
  if (!response.ok) {
    throw new Error("Login failed");
  }
  return response.json();
};

export const fetchSession = async () => {
  try {
    const response = await fetch(`${BASE_URL}/auth/session`, {
      credentials: "include",
    });
    if (!response.ok) {
      throw new Error("Failed to fetch session data.");
    }
    return await response.json();
  } catch (error) {
    throw error;
  }
};

export const logoutUser = async () => {
  const response = await fetch(`${BASE_URL}/auth/logout`, {
    method: "POST",
    credentials: "include",
    headers: {
      "Content-Type": "application/json",
    },
  });

  if (!response.ok) {
    throw new Error("Logout failed");
  }
  return response.json();
};

export const fetchEventDetail = async (eventId: string) => {
  try {
    const response = await fetch(`${BASE_URL}/events/${eventId}`, {
      method: "GET",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
    });
    if (!response.ok) {
      throw new Error("Failed to fetch event details.");
    }
    const data = await response.json();
    if (data.status !== "success" || data.data.length === 0) {
      throw new Error("No event details found.");
    }
    return data.data[0];
  } catch (error) {
    throw error;
  }
};
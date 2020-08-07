import DashboardLayout from "@/pages/Layout/DashboardLayout.vue";

import Dashboard from "@/pages/Dashboard.vue";
import UserProfile from "@/pages/UserProfile.vue";
import UpdateAgent from "@/pages/UpdateAgent.vue";
import DeployAgent from "@/pages/DeployAgent.vue";
import UserManagement from "@/pages/UserManagement.vue";
import Login from "@/pages/Login.vue";
import Failed from "@/pages/Failed.vue";
import Progress from "@/pages/Progress.vue";
import Completed from "@/pages/Completed.vue";
import Results from "@/pages/Results.vue";
import Logout from "@/pages/Logout.vue";

const routes = [
  {
    path: "/",
    component: DashboardLayout,
    redirect: "/login",
    children: [
      {
        path: "*dashboard",
        name: "Dashboard",
        component: Dashboard
      },
      {
        path: "user",
        name: "User Profile",
        component: UserProfile
      },
      {
        path: "update/agent",
        name: "Update Agent",
        component: UpdateAgent
      },
      {
        path: "deploy/agent",
        name: "Deploy Agent",
        component: DeployAgent
      },
      {
        path: "users",
        name: "User Management",
        component: UserManagement
      },
      {
        path: "failed",
        name: "Failed Scans",
        component: Failed
      },
      {
        path: "progress",
        name: "Running Scans",
        component: Progress
      },
      {
        path: "completed",
        name: "Completed Scans",
        component: Completed
      },
      {
        path: 'results/show/:id',
        name: "Loki Scan Results",
        component: Results
      },
      {
        path: 'logout',
        name: "Logout",
        component: Logout
      }
    ]
  },
  {
    path: "*/login",
    name: 'login',
    component: Login
  }
];

export default routes;

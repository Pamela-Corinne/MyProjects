package com.example.firebaseapp

import android.app.AlertDialog
import android.content.Intent
import android.os.Bundle
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.firebaseapp.databinding.ActivityAdminBinding
import com.google.firebase.auth.ktx.auth
import com.google.firebase.firestore.ktx.firestore
import com.google.firebase.ktx.Firebase

class AdminActivity : AppCompatActivity() {

    private lateinit var binding: ActivityAdminBinding
    private val db = Firebase.firestore
    private var isSidePanelVisible = false
    private lateinit var pendingRequestsAdapter: PendingRequestsAdapter

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAdminBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Set click listener for the root layout to close the side panel
        binding.root.setOnClickListener {
            if (isSidePanelVisible) {
                toggleSidePanel()
            }
        }

        // Make sure clicks on the side panel don't close it
        binding.sidePanel.setOnClickListener(null)

        binding.menuIcon.setOnClickListener { toggleSidePanel() }
        binding.updateAccountButton.setOnClickListener { showUpdateAccountDialog() }
        binding.pendingRequestsButton.setOnClickListener { showPendingRequestsDialog() }
        binding.logoutButton.setOnClickListener { logout() }

        // Placeholder for "View Child Profile" button.  Replace with actual implementation.
        binding.viewChildProfilesButton.setOnClickListener {
            // Create an Intent to start ViewChildProfilesActivity
            val intent = Intent(this, ViewChildProfilesActivity::class.java)
            // Start the activity
            startActivity(intent)
        }

    }

    private fun toggleSidePanel() {
        isSidePanelVisible = !isSidePanelVisible
        binding.sidePanel.visibility = if (isSidePanelVisible) View.VISIBLE else View.GONE
    }

    private fun showPendingRequestsDialog() {
        val dialogView = layoutInflater.inflate(R.layout.dialog_pending_requests, null)
        val recyclerView = dialogView.findViewById<RecyclerView>(R.id.dialogRecyclerView)
        val noRequestsText = dialogView.findViewById<TextView>(R.id.noRequestsTextView)

        recyclerView.layoutManager = LinearLayoutManager(this)

        // ✅ Initialize the class-level adapter
        pendingRequestsAdapter = PendingRequestsAdapter(this) { uid, action ->
            handlePendingRequest(uid, action, noRequestsText)
        }

        recyclerView.adapter = pendingRequestsAdapter

        val dialog = AlertDialog.Builder(this)
            .setView(dialogView)
            .setTitle("Pending Requests")
            .setNegativeButton("Close") { dialogInterface, _ -> dialogInterface.dismiss() }
            .create()

        dialog.show()

        db.collection("pendingUsers").get()
            .addOnSuccessListener { snapshot ->
                val requests = snapshot.documents.mapNotNull {
                    val email = it.getString("email") ?: return@mapNotNull null
                    PendingRequest(it.id, email)
                }
                pendingRequestsAdapter.updatePendingRequests(requests)
                noRequestsText.visibility = if (requests.isEmpty()) View.VISIBLE else View.GONE
            }
    }

    private fun handlePendingRequest(
        uid: String,
        action: String,
        noRequestsText: TextView
    ) {
        when (action) {
            "Approve" -> {
                db.collection("pendingUsers").document(uid).get()
                    .addOnSuccessListener { doc ->
                        val email = doc.getString("email") ?: return@addOnSuccessListener
                        val approvedData = hashMapOf("uid" to uid, "email" to email)
                        db.collection("approvedUsers").add(approvedData)
                            .addOnSuccessListener {
                                db.collection("pendingUsers").document(uid).delete()
                                    .addOnSuccessListener {
                                        showToast("User approved!")
                                        removeFromAdapter(uid, noRequestsText)
                                    }
                            }
                    }
            }
            "Reject" -> {
                db.collection("pendingUsers").document(uid).delete()
                    .addOnSuccessListener {
                        showToast("User rejected!")
                        removeFromAdapter(uid, noRequestsText)
                    }
            }
        }
    }

    // ✅ No need to pass adapter anymore
    private fun removeFromAdapter(uid: String, noRequestsText: TextView) {
        pendingRequestsAdapter.removeRequest(uid)
        if (pendingRequestsAdapter.itemCount == 0) {
            noRequestsText.visibility = View.VISIBLE
        }
    }

    private fun showUpdateAccountDialog() {
        AlertDialog.Builder(this)
            .setTitle("Update Account")
            .setMessage("This will allow you to update your admin account in the future.")
            .setPositiveButton("OK", null)
            .show()
    }

    private fun logout() {
        Firebase.auth.signOut()
        startActivity(Intent(this, LoginActivity::class.java))
        finish()
    }

    private fun showToast(message: String) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show()
    }
}

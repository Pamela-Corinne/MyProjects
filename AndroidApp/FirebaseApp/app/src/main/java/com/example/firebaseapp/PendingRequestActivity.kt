package com.example.firebaseapp

import android.os.Bundle
import android.util.Log
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.firebaseapp.databinding.ActivityPendingRequestBinding
import com.google.firebase.firestore.ktx.firestore
import com.google.firebase.ktx.Firebase

class PendingRequestsActivity : AppCompatActivity() {
    private lateinit var binding: ActivityPendingRequestBinding
    private val db = Firebase.firestore
    private lateinit var pendingRequestsAdapter: PendingRequestsAdapter

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPendingRequestBinding.inflate(layoutInflater)
        setContentView(binding.root)

        binding.pendingRequestsRecyclerView.layoutManager = LinearLayoutManager(this)
        pendingRequestsAdapter = PendingRequestsAdapter(this) { uid, action ->
            // Handle approve/reject actions (you might want to move this logic to AdminActivity)
        }
        binding.pendingRequestsRecyclerView.adapter = pendingRequestsAdapter

        loadPendingRequests()

        binding.backButton.setOnClickListener {
            finish()
        }
    }

    private fun loadPendingRequests() {
        db.collection("pendingUsers").get()
            .addOnSuccessListener { querySnapshot ->
                val pendingRequests = querySnapshot.documents.mapNotNull { document ->
                    val data = document.data
                    data?.get("email")?.toString()?.let { email ->
                        PendingRequest(document.id, email)
                    }
                }
                pendingRequestsAdapter.updatePendingRequests(pendingRequests)
            }
            .addOnFailureListener { exception ->
                showErrorToast("Error loading pending requests: ${exception.message}")
                Log.e("PendingRequestsActivity", "Error loading pending requests", exception)
            }
    }

    private fun showErrorToast(message: String) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show()
    }
}
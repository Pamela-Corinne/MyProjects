package com.example.firebaseapp

import android.content.Context
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Button
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView

// Adapter for Pending Requests
class PendingRequestsAdapter(
    private val context: Context,
    private val onRequestAction: (String, String) -> Unit
) : RecyclerView.Adapter<PendingRequestsAdapter.PendingRequestViewHolder>() {

    private val pendingRequests = mutableListOf<PendingRequest>()

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): PendingRequestViewHolder {
        val view = LayoutInflater.from(context).inflate(R.layout.item_pending_request, parent, false)
        return PendingRequestViewHolder(view)
    }

    override fun onBindViewHolder(holder: PendingRequestViewHolder, position: Int) {
        val request = pendingRequests[position]
        holder.emailTextView.text = request.email

        // Approve and Reject Button Actions
        holder.approveButton.setOnClickListener {
            onRequestAction(request.uid, "Approve")
        }
        holder.rejectButton.setOnClickListener {
            onRequestAction(request.uid, "Reject")
        }
    }

    override fun getItemCount(): Int {
        return pendingRequests.size
    }

    // Method to update the list of pending requests
    fun updatePendingRequests(requests: List<PendingRequest>) {
        pendingRequests.clear()
        pendingRequests.addAll(requests)
        notifyDataSetChanged()
    }

    // Method to remove a request from the adapter
    fun removeRequest(uid: String) {
        val position = pendingRequests.indexOfFirst { it.uid == uid }
        if (position != -1) {
            pendingRequests.removeAt(position)
            notifyItemRemoved(position)
        }
    }

    // ViewHolder class for each pending request item
    class PendingRequestViewHolder(view: View) : RecyclerView.ViewHolder(view) {
        val emailTextView: TextView = view.findViewById(R.id.emailTextView)
        val approveButton: Button = view.findViewById(R.id.approveButton)
        val rejectButton: Button = view.findViewById(R.id.rejectButton)
    }
}

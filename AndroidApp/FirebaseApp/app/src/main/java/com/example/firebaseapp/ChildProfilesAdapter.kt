package com.example.firebaseapp

import android.content.Context
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView

class ChildProfilesAdapter(private val context: Context) :
    RecyclerView.Adapter<ChildProfilesAdapter.ChildProfileViewHolder>() {

    private var childProfile: List<ChildProfile> = emptyList()

    inner class ChildProfileViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        val childNameTextView: TextView = itemView.findViewById(R.id.childNameTextView)
        val childAgeTextView: TextView = itemView.findViewById(R.id.childAgeTextView)
        val childHeightTextView: TextView = itemView.findViewById(R.id.childHeightTextView)
        val childWeightTextView: TextView = itemView.findViewById(R.id.childWeightTextView)
        val childGenderTextView: TextView = itemView.findViewById(R.id.childGenderTextView)
        val childClassificationTextView: TextView = itemView.findViewById(R.id.childClassificationTextView)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ChildProfileViewHolder {
        val view = LayoutInflater.from(context).inflate(R.layout.item_child_profile, parent, false)
        return ChildProfileViewHolder(view)
    }

    override fun onBindViewHolder(holder: ChildProfileViewHolder, position: Int) {
        val profile = childProfile.getOrNull(position) ?: return
        holder.childNameTextView.text = profile.name
        holder.childAgeTextView.text = "Age: ${profile.age}"
        holder.childHeightTextView.text = "Height: ${profile.height}"
        holder.childWeightTextView.text = "Weight: ${profile.weight}"
        holder.childGenderTextView.text = "Gender: ${profile.gender}"
        holder.childClassificationTextView.text = "Classification: ${profile.classification}"
    }

    override fun getItemCount(): Int = childProfile.size

    fun updateChildProfiles(newChildProfiles: List<ChildProfile>) {
        childProfile = newChildProfiles
        notifyDataSetChanged()
    }
}

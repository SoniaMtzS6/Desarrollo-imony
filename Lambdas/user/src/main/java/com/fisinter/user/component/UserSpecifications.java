package com.fisinter.user.component;

import org.springframework.data.jpa.domain.Specification;

import com.fisinter.user.entity.User;

import jakarta.persistence.criteria.CriteriaBuilder;
import jakarta.persistence.criteria.CriteriaQuery;
import jakarta.persistence.criteria.Root;

import java.util.Arrays;

public class UserSpecifications {
    
	// Filtro para el atributo 'id(user_id)' con búsqueda parcial
    public static Specification<User> idIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("id").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("id"), "%" + values[0] + "%") 
               : null;
        };
    }
    
	// Filtro para el atributo 'name' con búsqueda parcial
    public static Specification<User> nameIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("name").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("name"), "%" + values[0] + "%") 
               : null;
        };
    }

    // Filtro para el atributo 'surname' con búsqueda parcial
    public static Specification<User> surnameIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("surname").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("surname"), "%" + values[0] + "%") 
               : null;
        };
    }
    
    // Filtro para el atributo 'identificationType' con búsqueda parcial
    public static Specification<User> identificationTypeIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("identificationType").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("identificationType"), "%" + values[0] + "%") 
               : null;
        };
    }

    // Filtro para el atributo 'identificationValue'
    public static Specification<User> identificationValueIn(Long[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("identificationValue").in(Arrays.asList(values));
        };
    }
    
    // Filtro para el atributo 'identificationType' con búsqueda parcial
    public static Specification<User> birthdateIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("birthdate").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("birthdate"), "%" + values[0] + "%") 
               : null;
        };
    }
    
    // Filtro para el atributo 'identificationType' con búsqueda parcial
    public static Specification<User> genderIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("gender").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("gender"), "%" + values[0] + "%") 
               : null;
        };
    }

    // Filtro para el atributo 'identificationType' con búsqueda parcial
    public static Specification<User> emailIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("email").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("email"), "%" + values[0] + "%") 
               : null;
        };
    }
    
    // Filtro para el atributo 'identificationType' con búsqueda parcial
    public static Specification<User> phoneIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("phone").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("phone"), "%" + values[0] + "%") 
               : null;
        };
    }
    
    // Filtro para el atributo 'identificationType' con búsqueda parcial
    public static Specification<User> taxIdentificationTypeIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("taxIdentificationType").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("taxIdentificationType"), "%" + values[0] + "%") 
               : null;
        };
    }

    // Filtro para el atributo 'taxIdentificationValue'
    public static Specification<User> taxIdentificationValueIn(Long[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("taxIdentificationValue").in(Arrays.asList(values));
        };
    }
    
    // Filtro para el atributo 'nationality' con búsqueda parcial
    public static Specification<User> nationalityIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("nationality").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("nationality"), "%" + values[0] + "%") 
               : null;
        };
    }
    
    // Filtro para el atributo 'taxCondition' con búsqueda parcial
    public static Specification<User> taxConditionIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("taxCondition").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("taxCondition"), "%" + values[0] + "%") 
               : null;
        };
    }

    // Filtro para el atributo 'status' con búsqueda parcial
    public static Specification<User> statusIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("status").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("status"), "%" + values[0] + "%") 
               : null;
        };
    }
    
    // Filtro para el atributo 'operationCountry' con búsqueda parcial
    public static Specification<User> operationCountryIn(String[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("operationCountry").in(Arrays.asList(values)) != null 
               ? builder.like(root.get("operationCountry"), "%" + values[0] + "%") 
               : null;
        };
    }
    
    // Filtro para el atributo 'idEmpresa'
    public static Specification<User> idEmpresaIn(Long[] values) {
        return (Root<User> root, CriteriaQuery<?> query, CriteriaBuilder builder) -> {
            if (values == null || values.length == 0) return null;
            return root.get("idEmpresa").in(Arrays.asList(values));
        };
    }
    
}
